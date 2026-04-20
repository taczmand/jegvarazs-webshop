<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AmountToText;
use App\Http\Controllers\Controller;
use App\Mail\NewContract;
use App\Models\Category;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Contract;
use App\Models\ContractProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ContractController extends Controller
{
    public function index()
    {
        $version_files = Storage::disk('local')->files('contract_versions');

        $versions = collect($version_files)
            ->filter(fn($file) => str_ends_with($file, '.json'))
            ->map(fn($file) => basename($file, '.json'))
            ->values();

        $users = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('admin.business.contracts', [
            'versions' => $versions,
            'users' => $users,
        ]);
    }

    private function normalizeContractData($contractData): array
    {
        $data = is_array($contractData) ? $contractData : [];

        if (!array_key_exists('deposit_due_date', $data)) {
            $data['deposit_due_date'] = null;
        }

        $depositMethod = isset($data['deposit_payment_method']) ? trim((string) $data['deposit_payment_method']) : '';
        if ($depositMethod === 'Készpénz') {
            $data['deposit_due_date'] = null;
        }

        return $data;
    }

    public function data(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('view-contracts') && !$user->can('view-own-contracts'))) {
            abort(403);
        }

        $contracts = Contract::select([
            'contracts.id',
            'contracts.name',
            'contracts.country',
            'contracts.zip_code',
            'contracts.city',
            'contracts.address_line',
            'contracts.installation_date',
            'contracts.created_at as created',
            'users.name as creator_name',
            'contracts.viewed_by',
            'contracts.viewed_at',
        ])
            ->leftJoin('users', 'contracts.created_by', '=', 'users.id');

        if ($user->can('view-own-contracts')) {
            $contracts->where('contracts.created_by', $user->id);
        }

        return DataTables::of($contracts)
            ->editColumn('created_at', function ($contract) {
                return $contract->created_at
                    ? \Carbon\Carbon::parse($contract->created_at)->format('Y-m-d H:i:s')
                    : '';
            })
            ->editColumn('installation_date', function ($contract) {
                return $contract->installation_date
                    ? \Carbon\Carbon::parse($contract->installation_date)->format('Y-m-d')
                    : '';
            })
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('contracts.id', '=', $keyword);
                }
            })
            ->addColumn('creator_name', function ($contract) {
                return $contract->creator_name ?? 'Ismeretlen';
            })
            ->addColumn('viewed_by', function ($contract) {
                if ($contract->viewed_by) {
                    return '<span title="Megtekintve: '
                        . ($contract->viewed_at ? \Carbon\Carbon::parse($contract->viewed_at)->format('Y-m-d H:i:s') : '-')
                        . '">' . e($contract->viewed_by) . '</span>';
                }
                return '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->addColumn('action', function ($contract) {
                $user = auth('admin')->user();

                $buttons = '
                <button class="btn btn-sm btn-primary view" data-id="' . $contract->id . '" title="Megtekintés">
                    <i class="fas fa-eye"></i>
                </button>
            ';
                if ($user && $user->can('edit-contract')) {
                    $buttons .= '
                    <button class="btn btn-sm btn-warning edit" data-id="' . $contract->id . '" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>';
                }

                if ($user && $user->can('delete-contract')) {
                    $buttons .= '
                    <button class="btn btn-sm btn-danger delete" data-id="' . $contract->id . '" title="Törlés">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
                }

                return $buttons;
            })
            ->setRowClass(function ($contract) {
                return $contract->viewed_by ? '' : 'fw-bold';
            })
            ->rawColumns(['action', 'viewed_by'])
            ->make(true);
    }


    public function getVersionJson($version)
    {
        $path = "contract_versions/{$version}.json";

        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['error' => 'Verzió nem található'], 404);
        }

        $json = Storage::disk('local')->get($path);
        if (!$json) {
            return response()->json(['error' => 'Hiba történt a fájl betöltésekor'], 500);
        }

        return response()->json(json_decode($json, true));
    }
    public function store(Request $request)
    {
        // Validáció
        $request->validate([
            'client_id' => 'nullable|integer|exists:clients,id',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'create_client' => 'nullable|boolean',
            'client_address_id' => 'nullable|integer',
            'use_custom_address' => 'nullable|boolean',
            'contract_version' => 'required|string',
            'contact_name' => 'required|string|max:255',
            'contact_country' => 'required|string|max:100',
            'contact_zip_code' => 'required|string|max:20',
            'contact_city' => 'required|string|max:100',
            'contact_address_line' => 'required|string|max:255',
            'installation_date' => 'required|date',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'mothers_name' => 'nullable|string|max:255',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'id_number' => 'nullable|string|max:50',
            'contract_data.price' => ['required', 'numeric', 'min:0'],
            'products' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (!collect($value)->contains(fn($p) => isset($p['selected']) && $p['selected'] == 1)) {
                        $fail('Legalább egy terméket ki kell választanod.');
                    }
                }
            ],
            'products.*.selected' => ['in:1'],
            'created_by' => 'nullable|integer|exists:users,id',
        ], [
            'contract_version.required' => 'A szerződés verzió megadása kötelező.',
            'contact_name.required' => 'A kapcsolattartó nevét kötelező megadni.',
            'contact_country.required' => 'A kapcsolattartó országát kötelező megadni.',
            'contact_zip_code.required' => 'Az irányítószám megadása kötelező.',
            'contact_city.required' => 'A város megadása kötelező.',
            'contact_address_line.required' => 'A pontos cím megadása kötelező.',
            'installation_date.required' => 'A telepítés dátumát kötelező megadni.',
            'contact_email.email' => 'Kérlek, érvényes e-mail címet adj meg.',
            'contract_data.price.required' => 'Az ár megadása kötelező.',
            'contract_data.price.numeric' => 'Az árnak számnak kell lennie.',
            'contract_data.price.min' => 'Az ár nem lehet negatív.',
            'products.required' => 'Legalább egy termék adatát meg kell adni.',
            'products.array' => 'A termékeknek tömb formátumban kell érkezniük.',
            'products.*.selected.in' => 'Csak a kijelölt termékek lehetnek érvényesek.',
        ]);

        DB::beginTransaction();

        try {
            $clientId = $request->input('client_id');
            $shouldCreateClient = (bool) $request->input('create_client');
            $clientAddressId = $request->input('client_address_id');
            $useCustomAddress = (bool) $request->input('use_custom_address');

            $contactEmail = $request->input('contact_email');
            $contactEmail = is_string($contactEmail) ? trim($contactEmail) : null;
            $contactEmail = $contactEmail !== '' ? mb_strtolower($contactEmail) : null;

            $warnings = [];
            $contactEmailConflicts = false;

            $client = null;
            $address = null;

            if ($shouldCreateClient) {
                if (!is_null($contactEmail)) {
                    $existingClient = Client::query()
                        ->select(['id', 'name', 'email', 'phone', 'id_number'])
                        ->where('email', $contactEmail)
                        ->first();

                    if ($existingClient) {
                        $contactEmailConflicts = true;
                        $warnings[] = 'Ez az e-mail cím már másik ügyfélhez tartozik, ezért az új ügyfélhez nem mentettük el, csak a szerződéshez fogjuk használni.';
                    }
                }

                $client = Client::create([
                    'name' => $request->input('contact_name') ?: null,
                    'mothers_name' => $request->input('mothers_name') ?: null,
                    'place_of_birth' => $request->input('place_of_birth') ?: null,
                    'date_of_birth' => $request->input('date_of_birth') ?: null,
                    'id_number' => $request->input('id_number') ?: null,
                    'email' => $contactEmailConflicts ? null : $contactEmail,
                    'phone' => $request->input('contact_phone') ?: null,
                    'comment' => null,
                ]);

                $address = ClientAddress::create([
                    'client_id' => $client->id,
                    'country' => $request->input('contact_country') ?: 'HU',
                    'zip_code' => $request->input('contact_zip_code') ?: null,
                    'city' => $request->input('contact_city') ?: null,
                    'address_line' => $request->input('contact_address_line') ?: null,
                    'comment' => null,
                    'is_default' => true,
                ]);

                $request->merge([
                    'client_id' => $client->id,
                    'create_client' => false,
                    'client_address_id' => $address->id,
                    'use_custom_address' => false,
                ]);
            } elseif ($clientId) {
                $client = Client::find($clientId);

                if ($clientAddressId) {
                    $address = ClientAddress::query()
                        ->where('client_id', $client->id)
                        ->where('id', $clientAddressId)
                        ->first();
                }

                if (!$address && !$useCustomAddress) {
                    $address = ClientAddress::query()
                        ->where('client_id', $client->id)
                        ->orderByDesc('is_default')
                        ->orderByDesc('id')
                        ->first();
                }

                if (!$address && $useCustomAddress) {
                    $existingAddress = ClientAddress::query()
                        ->where('client_id', $client->id)
                        ->where('country', $request->input('contact_country') ?: 'HU')
                        ->where('zip_code', $request->input('contact_zip_code') ?: null)
                        ->where('city', $request->input('contact_city') ?: null)
                        ->where('address_line', $request->input('contact_address_line') ?: null)
                        ->first();

                    if (!$existingAddress) {
                        $hasDefault = ClientAddress::where('client_id', $client->id)->where('is_default', true)->exists();

                        $existingAddress = ClientAddress::create([
                            'client_id' => $client->id,
                            'country' => $request->input('contact_country') ?: 'HU',
                            'zip_code' => $request->input('contact_zip_code') ?: null,
                            'city' => $request->input('contact_city') ?: null,
                            'address_line' => $request->input('contact_address_line') ?: null,
                            'comment' => null,
                            'is_default' => !$hasDefault,
                        ]);
                    }

                    $address = $existingAddress;
                }

                if ($client) {
                    $client->update([
                        'name' => $request->input('contact_name') ?: $client->name,
                        'email' => $client->email,
                        'phone' => $request->input('contact_phone') ?: $client->phone,
                        'mothers_name' => $request->input('mothers_name') ?: $client->mothers_name,
                        'place_of_birth' => $request->input('place_of_birth') ?: $client->place_of_birth,
                        'date_of_birth' => $request->input('date_of_birth') ?: $client->date_of_birth,
                        'id_number' => $request->input('id_number') ?: $client->id_number,
                    ]);
                }

                if ($address && !$useCustomAddress) {
                    $address->update([
                        'country' => $request->input('contact_country') ?: $address->country,
                        'zip_code' => $request->input('contact_zip_code') ?: $address->zip_code,
                        'city' => $request->input('contact_city') ?: $address->city,
                        'address_line' => $request->input('contact_address_line') ?: $address->address_line,
                    ]);
                }
            }

            $resolvedName = $request->input('contact_name');
            $resolvedEmail = $contactEmail;
            $resolvedPhone = $request->input('contact_phone');
            $resolvedCountry = $request->input('contact_country');
            $resolvedZip = $request->input('contact_zip_code');
            $resolvedCity = $request->input('contact_city');
            $resolvedAddressLine = $request->input('contact_address_line');

            if ($client) {
                $resolvedName = $client->name;
                if (empty($resolvedEmail)) {
                    $resolvedEmail = $client->email;
                }
                $resolvedPhone = $client->phone;
            }

            if ($address) {
                $resolvedCountry = $address->country;
                $resolvedZip = $address->zip_code;
                $resolvedCity = $address->city;
                $resolvedAddressLine = $address->address_line;
            }

            // Aláírás mentése, ha van
            $signatureName = null;
            if ($request->filled('signature')) {
                $base64 = $request->input('signature');
                if (preg_match('/^data:image\/png;base64,/', $base64)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $base64 = base64_decode($base64);
                    $signatureName = 'signature_' . time() . '_' . uniqid() . '.png';
                    Storage::disk('local')->put("signatures/{$signatureName}", $base64);
                }
            }

            // Ha van contract_id → frissítünk
            if ($request->filled('contract_id')) {
                $contract = Contract::findOrFail($request->input('contract_id'));

                $contract->update([
                    'client_id' => $request->input('client_id') ?: null,
                    'lead_id' => $request->input('lead_id') ?: null,
                    'version' => $request->input('contract_version'),
                    'name' => $resolvedName,
                    'country' => $resolvedCountry,
                    'zip_code' => $resolvedZip,
                    'city' => $resolvedCity,
                    'address_line' => $resolvedAddressLine,
                    'installation_date' => $request->input('installation_date'),
                    'phone' => $resolvedPhone,
                    'email' => $resolvedEmail,
                    'mothers_name' => $request->input('mothers_name') ?? null,
                    'place_of_birth' => $request->input('place_of_birth') ?? null,
                    'date_of_birth' => $request->input('date_of_birth') ?? null,
                    'id_number' => $request->input('id_number'),
                    'data' => $this->normalizeContractData($request->input('contract_data', []))
                ]);

                if ($request->filled('signature')) {
                    $contract->update([
                        'signature_path' => $signatureName
                    ]);
                } else {
                    $signatureName = $contract->signature_path;
                }

                // Régi termékek törlése és újak mentése
                $contract->products()->detach();


                $products = [];
                foreach ($request->input('products') as $productId => $data) {
                    if (!isset($data['selected'])) {
                        continue;
                    }

                    $item = ContractProduct::create([
                        'contract_id' => $contract->id,
                        'product_id' => $productId,
                        'product_qty' => $data['product_qty'],
                        'gross_price' => $data['gross_price'],
                    ]);

                    $products[] = [
                        'title' => Product::findOrFail($productId)->title ?? "N/A",
                        'gross_price' => $item->gross_price,
                        'product_qty' => $item->product_qty
                    ];
                }

                $totalGross = collect($products)->sum(fn($item) => $item['gross_price'] * $item['product_qty']);

                $pdf_data = [
                    'contract' => $contract->toArray(),
                    'products' => $products,
                    'total_gross' => $totalGross,
                    'total_gross_text' => AmountToText::convert($totalGross),
                    'data' => $contract->data,
                    'signature_path' => $signatureName ? storage_path("app/private/signatures/{$signatureName}") : null,
                ];

                // PDF újragenerálás
                $pdf = Pdf::loadView('pdf.contract_' . $request->get('contract_version'), $pdf_data);
                $file_name = 'contract_' . $contract->id . '.pdf';
                Storage::put("contracts/{$file_name}", $pdf->output());
                $contract->update(['pdf_path' => "contracts/{$file_name}"]);

                // Munkalap módosítása
                $worksheet = Worksheet::where('contract_id', $contract->id)->first();
                if ($worksheet) {
                    $worksheet->update([
                        'client_id' => $contract->client_id,
                        'work_name' => "Szerződéses munkalap - {$contract->name}",
                        'work_type' => "Szerelés",
                        'name' => $contract->name,
                        'email' => $contract->email,
                        'phone' => $contract->phone,
                        'country' => $contract->country,
                        'zip_code' => $contract->zip_code,
                        'city' => $contract->city,
                        'address_line' => $contract->address_line,
                        'installation_date' => $contract->installation_date
                    ]);

                    // Munkalap termékek módosítása
                    if ($request->has('products')) {
                        $worksheet->products()->detach();

                        foreach ($request->input('products') as $productId => $data) {
                            if (!isset($data['selected'])) {
                                continue;
                            }

                            WorksheetProduct::create([
                                'worksheet_id' => $worksheet->id,
                                'product_id' => $productId,
                                'quantity' => $data['product_qty']
                            ]);
                        }
                    }

                }

                DB::commit();

                if ($contract->email) {
                    $mail = Mail::to($contract->email);
                    $mail->send(new NewContract($contract));
                }

                if (!app()->environment('local')) {
                    $mail_to_office = Mail::to('jegvarazsiroda@gmail.com');
                    $mail_to_office->send(new NewContract($contract));
                }

                return response()->json([
                    'message' => 'Szerződés frissítve!',
                    'warnings' => $warnings,
                    'data' => [
                        'contract' => $contract,
                        'pdf_path' => Storage::url($contract->pdf_path),
                    ]
                ], 200);
            } else {


                // Ha nincs contract_id → új szerződés és munkalap
                $user = auth('admin')->user();
                $creatorId = auth('admin')->id();
                if (
                    $user
                    && $user->can('select-contract-creator')
                    && $request->filled('created_by')
                ) {
                    $creatorId = (int) $request->input('created_by');
                }

                $contract = Contract::create([
                    'client_id' => $request->input('client_id') ?: null,
                    'lead_id' => $request->input('lead_id') ?: null,
                    'version' => $request->input('contract_version'),
                    'name' => $resolvedName,
                    'country' => $resolvedCountry,
                    'zip_code' => $resolvedZip,
                    'city' => $resolvedCity,
                    'address_line' => $resolvedAddressLine,
                    'installation_date' => $request->input('installation_date'),
                    'phone' => $resolvedPhone,
                    'email' => $resolvedEmail,
                    'mothers_name' => $request->input('mothers_name') ?? null,
                    'place_of_birth' => $request->input('place_of_birth') ?? null,
                    'date_of_birth' => $request->input('date_of_birth') ?? null,
                    'id_number' => $request->input('id_number'),
                    'data' => $this->normalizeContractData($request->input('contract_data', [])),
                    'signature_path' => "{$signatureName}",
                    'created_by' => $creatorId,
                ]);

                $products = [];

                foreach ($request->input('products') as $productId => $data) {
                    if (!isset($data['selected'])) {
                        continue;
                    }

                    $item = ContractProduct::create([
                        'contract_id' => $contract->id,
                        'product_id' => $productId,
                        'product_qty' => $data['product_qty'],
                        'gross_price' => $data['gross_price'],
                    ]);

                    $products[] = [
                        'title' => Product::findOrFail($productId)->title ?? "N/A",
                        'gross_price' => $item->gross_price,
                        'product_qty' => $item->product_qty
                    ];
                }

                $totalGross = collect($products)->sum(fn($item) => $item['gross_price'] * $item['product_qty']);

                $pdf_data = [
                    'contract' => $contract->toArray(),
                    'products' => $products,
                    'total_gross' => $totalGross,
                    'total_gross_text' => AmountToText::convert($totalGross),
                    'data' => $contract->data,
                    'signature_path' => $signatureName ? storage_path("app/private/signatures/{$signatureName}") : null,
                ];

                // PDF generálása
                $pdf = Pdf::loadView('pdf.contract_' . $request->get('contract_version'), $pdf_data);

                $file_name = 'contract_' . $contract->id . '.pdf';
                Storage::put("contracts/{$file_name}", $pdf->output());

                $contract->update(['pdf_path' => "contracts/{$file_name}"]);

                // Munkalap létrehozása
                $worksheet = Worksheet::create([
                    'client_id' => $contract->client_id,
                    'work_name' => "Szerződéses munkalap - {$contract->name}",
                    'work_type' => "Szerelés",
                    'name' => $contract->name,
                    'email' => $contract->email,
                    'phone' => $contract->phone,
                    'country' => $contract->country,
                    'zip_code' => $contract->zip_code,
                    'city' => $contract->city,
                    'address_line' => $contract->address_line,
                    'installation_date' => $contract->installation_date,
                    'work_status' => "Folyamatban",
                    'contract_id' => $contract->id,
                    'created_by' => $creatorId,
                ]);

                foreach ($request->input('products') as $productId => $data) {
                    if (!isset($data['selected'])) {
                        continue;
                    }

                    WorksheetProduct::create([
                        'worksheet_id' => $worksheet->id,
                        'product_id' => $productId,
                        'quantity' => $data['product_qty']
                    ]);
                }
            }

            DB::commit();

            if ($contract->email) {
                $mail = Mail::to($contract->email);
                $mail->send(new NewContract($contract));
            }

            if (!app()->environment('local')) {
                $mail_to_office = Mail::to('jegvarazsiroda@gmail.com');
                $mail_to_office->send(new NewContract($contract));
            }

            return response()->json([
                'message' => 'Sikeres generálás!',
                'warnings' => $warnings,
                'data' => [
                    'contract' => $contract,
                    'worksheet' => $worksheet,
                    'pdf_path' => Storage::url($contract->pdf_path),
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Szerződés mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchWithCategories() {
        $categories = Category::whereHas('products', function($query) {
            $query->where('is_offerable', 1);
        })
            ->with(['products' => function($query) {
                $query->where('is_offerable', 1);
            }])
            ->orderBy('title')
            ->get();

        return response()->json($categories);
    }

    public function showProductsToContract($id)
    {
        $contract = Contract::with(['products'])->findOrFail($id);

        return response()->json([
            'contract' => $contract
        ]);
    }

    public function getPdf(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $products = $contract->products->map(function ($item) {
            return [
                'title' => $item->title ?? "N/A",
                'gross_price' => $item->pivot->gross_price,
                'product_qty' => $item->pivot->product_qty
            ];
        })->toArray();

        $signatureName = $contract->signature_path;

        $totalGross = collect($products)->sum(function ($item) {
            return $item['gross_price'] * $item['product_qty'];
        });

        $pdf_data = [
            'contract' => $contract->toArray(),
            'products' => $products,
            'data' => $contract->data,
            'total_gross' => $totalGross,
            'total_gross_text' => AmountToText::convert($totalGross),
            'signature_path' => $signatureName ? storage_path("app/private/signatures/{$signatureName}") : null,
        ];

        // PDF generálása
        $pdf = Pdf::loadView('pdf.contract_'.$contract->version, $pdf_data);
        return $pdf->stream('contract.pdf');
    }


    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        // Ellenőrizzük, hogy van-e kapcsolódó munkalap
        if ($contract->worksheets()->exists()) {
            return response()->json([
                'message' => 'A szerződés nem törölhető, mert kapcsolódó munkalapok vannak.'
            ], 400);
        }

        // Kapcsolódó termékek törlése
        $contract->products()->detach();

        // Aláírás fájl törlése, ha létezik
        if ($contract->signature_path && Storage::disk('local')->exists("signatures/{$contract->signature_path}")) {
            Storage::disk('local')->delete("signatures/{$contract->signature_path}");
        }

        // PDF fájl törlése, ha létezik
        if ($contract->pdf_path && Storage::disk('local')->exists($contract->pdf_path)) {
            Storage::disk('local')->delete($contract->pdf_path);
        }

        // Szerződés törlése
        $contract->delete();

        return response()->json([
            'message' => 'Szerződés sikeresen törölve.'
        ], 200);
    }

    public function previewPdf(Request $request) {

        $user = auth('admin')->user();
        $creatorId = auth('admin')->id();
        if (
            $user
            && $user->can('select-contract-creator')
            && $request->filled('created_by')
        ) {
            $creatorId = (int) $request->input('created_by');
        }

        $contract = array(
            'version' => $request->input('contract_version'),
            'name' => $request->input('contact_name') . ' szerződés',
            'country' => $request->input('contact_country'),
            'zip_code' => $request->input('contact_zip_code'),
            'city' => $request->input('contact_city'),
            'address_line' => $request->input('contact_address_line'),
            'installation_date' => $request->input('installation_date'),
            'phone' => $request->input('contact_phone'),
            'email' => $request->input('contact_email'),
            'mothers_name' => $request->input('mothers_name') ?? null,
            'place_of_birth' => $request->input('place_of_birth') ?? null,
            'date_of_birth' => $request->input('date_of_birth') ?? null,
            'id_number' => $request->input('id_number'),
            'data' => $this->normalizeContractData($request->input('contract_data', [])),
            'created_by' => $creatorId
        );

        $products = [];

        foreach ($request->input('products') as $productId => $data) {
            if (!isset($data['selected'])) {
                continue;
            }

            $products[] = [
                'title' => Product::findOrFail($productId)->title ?? "N/A",
                'gross_price' => $data['gross_price'],
                'product_qty' => $data['product_qty']
            ];
        }

        $totalGross = collect($products)->sum(fn($item) => $item['gross_price'] * $item['product_qty']);

        $pdf_data = [
            'contract' => $contract,
            'products' => $products,
            'total_gross' => $totalGross,
            'total_gross_text' => AmountToText::convert($totalGross),
            'data' => $contract['data'],
        ];

        // PDF generálása előnézetre
        $pdf = Pdf::loadView('pdf.contract_'.$contract['version'], $pdf_data);
        return $pdf->stream('contract.pdf');

    }

}
