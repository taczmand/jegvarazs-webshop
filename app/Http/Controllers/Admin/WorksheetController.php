<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Worksheet;
use App\Models\User;
use App\Models\Contract;
use App\Models\WorksheetImage;
use App\Models\WorksheetProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WorksheetController extends Controller
{
    protected $user = null;
    public function __construct()
    {
        $this->user = Auth::guard('admin')->user();
    }

    public function index()
    {
        $contracts = Contract::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.business.worksheets', [
            'contracts' => $contracts,
            'users' => $users
        ]);
    }

    public function getWorksheetsByWeek(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Missing date range'], 400);
        }

        $user = auth('admin')->user(); // vagy auth()->user(), ha nincs guard
        //dd($user);

        // Jogosultságellenőrzés
        if ($user->can('view-worksheets')) {
            $query = Worksheet::with(['worker:id,name'])
                ->whereBetween('installation_date', [$startDate, $endDate]);
        } elseif ($user->can('view-own-worksheets')) {
            $query = Worksheet::with(['worker:id,name'])
                ->where('worker_id', $user->id)
                ->whereBetween('installation_date', [$startDate, $endDate]);
        } else {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $worksheets = $query->get();

        $result = $worksheets->map(function ($worksheet) {
            return [
                'id' => $worksheet->id,
                'name' => $worksheet->name,
                'city' => $worksheet->city,
                'work_name' => $worksheet->work_name,
                'work_status' => $worksheet->work_status,
                'installation_date' => $worksheet->installation_date,
                'worker_name' => optional($worksheet->worker)->name,
            ];
        });

        return response()->json($result);
    }





    public function data()
    {
        $user = $this->user;

        // Jogosultságellenőrzés
        if ($user->can('view-worksheets')) {
            $worksheets = Worksheet::select([
                'worksheets.id',
                'worksheets.installation_date',
                'worksheets.name',
                'worksheets.city',
                'worksheets.work_type',
                'worksheets.work_status',
                'worksheets.data',
                'worksheets.contract_id',
                'worksheets.created_at as created',
                'creator.name as creator_name',
                'worker.name as worker_name',
            ])
                ->leftJoin('users as creator', 'worksheets.created_by', '=', 'creator.id')
                ->leftJoin('users as worker', 'worksheets.worker_id', '=', 'worker.id');
        } elseif ($user->can('view-own-worksheets')) {
            $worksheets = Worksheet::select([
                'worksheets.id',
                'worksheets.installation_date',
                'worksheets.name',
                'worksheets.city',
                'worksheets.work_name',
                'worksheets.work_type',
                'worksheets.work_status',
                'worksheets.data',
                'worksheets.contract_id',
                'worksheets.created_at as created',
                'creator.name as creator_name',
                'worker.name as worker_name',
            ])
                ->leftJoin('users as creator', 'worksheets.created_by', '=', 'creator.id')
                ->leftJoin('users as worker', 'worksheets.worker_id', '=', 'worker.id')
                ->where('worksheets.worker_id', $user->id);
        } else {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return DataTables::of($worksheets)
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('worksheets.id', '=', $keyword);
                }
            })
            ->addColumn('data', function ($worksheet) {
                $data = $worksheet->data;

                if (is_string($data)) {
                    $json = json_decode($data, true);
                } elseif (is_array($data)) {
                    $json = $data;
                } else {
                    $json = [];
                }

                if (!is_array($json) || empty($json)) {
                    return '-';
                }

                $html = '';
                foreach ($json as $key => $value) {
                    [$label, $translatedValue] = $this->translateDataEntry($key, $value);
                    $html .= "<div><strong>{$label}:</strong> {$translatedValue}</div>";
                }

                return $html;
            })

            ->filterColumn('data', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('worksheets.data', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('work_status', function ($query, $keyword) {
                $query->where('worksheets.work_status', '=', "{$keyword}");
            })
            ->addColumn('contract_id', function ($worksheet) {
                if ($worksheet->contract_id) {
                    return '<a class="btn btn-sm btn-info" target="_blank" href="' . route('admin.contracts.index', ['modal' => true, 'id' => $worksheet->contract_id]) . '"><i class="fa-solid fa-link"></i></a>';
                }
                return "-";
            })
            /*->addColumn('work_status_icon', function ($worksheet) {
                return match($worksheet->work_status) {
                    'Folyamatban' => '<i class="fas fa-tools text-danger" title="Folyamatban"></i>',
                    'Kész' => '<i class="fas fa-check text-warning" title="Kész"></i>',
                    'Lezárva' => '<i class="fas fa-check-double text-success" title="Lezárva"></i>',
                    default => '<i class="fas fa-question-circle text-muted" title="Ismeretlen státusz"></i>',
                };
            })*/
            ->addColumn('creator_name', fn($worksheet) => $worksheet->creator_name ?? '-')
            ->addColumn('worker_name', fn($worksheet) => $worksheet->worker_name ?? '-')
            ->addColumn('action', function ($worksheet) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-worksheet')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $worksheet->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-worksheet')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $worksheet->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['contract_id', 'action', 'work_status_icon', 'data'])
            ->make(true);
    }

    protected function translateDataEntry(string $key, $value): array
    {
        $keyTranslations = [
            'pipe' => 'Plusz cső',
            'console' => 'Konzol',
            'device_qty' => 'Készülékek',
            'exist_contract' => 'Szerződéskötés',
            'cleaning_type' => 'Tisztítás',
            'self_installation' => 'Saját telepítés'
        ];

        $valueTranslations = [
            'exist_contract' => [
                'hitel' => 'Hitelre lesz',
                'igen' => 'Igen',
                'nem' => 'Nem',
            ],
            'cleaning_type' => [
                'basic_clean' => 'Alaptisztítás',
                'full_clean' => 'Teljes mosás',
            ],
            'self_installation' => [
                'igen' => 'Igen',
                'nem' => 'Nem',
            ]
        ];

        $translatedKey = $keyTranslations[$key] ?? ucfirst(str_replace('_', ' ', $key));

        $translatedValue = $value;
        if (isset($valueTranslations[$key]) && isset($valueTranslations[$key][$value])) {
            $translatedValue = $valueTranslations[$key][$value];
        } elseif (is_null($value) || $value === '') {
            $translatedValue = '-';
        }

        return [$translatedKey, $translatedValue];
    }


    public function store(Request $request)
    {
        // Validáció
        $request->validate([
            'work_name' => 'required|string',
            'installation_date' => 'required|date',
            'contact_name' => 'required|string|max:255',
            'contact_country' => 'required|string|max:100',
            'contact_zip_code' => 'required|string|max:20',
            'contact_city' => 'required|string|max:100',
            'contact_address_line' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'work_status' => 'required',
            'work_type' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $worksheetId = $request->input('worksheet_id');

            $data = [
                'work_name'         => $request->input('work_name'),
                'work_type'         => $request->input('work_type'),
                'name'              => $request->input('contact_name'),
                'email'             => $request->input('contact_email'),
                'phone'             => $request->input('contact_phone'),
                'country'           => $request->input('contact_country'),
                'zip_code'          => $request->input('contact_zip_code'),
                'city'              => $request->input('contact_city'),
                'address_line'      => $request->input('contact_address_line'),
                'description'       => $request->input('contact_description') ?? null,
                'worker_report'     => $request->input('worker_report') ?? null,
                'installation_date' => $request->input('installation_date'),
                'worker_id'         => $request->input('worker_id') ?? null,
                'data'              => array_filter($request->input('extra_data') ?? [], fn($value) => !is_null($value)),
                'payment_method'    => $request->input('payment_method') ?? null,
                'payment_amount'    => $request->input('payment_amount') ?? 0,
                'work_status'       => $request->input('work_status'),
                'contract_id'       => $request->input('contract_id') ?? null
            ];

            // Ha "Kész" státuszba mentik
            if ($request->input('work_status') === 'Kész') {

                $paymentMethod = $request->input('payment_method');
                $paymentAmount = $request->input('maintenance_payment_amount');
                $workType = $request->input('work_type');

                if ("Szerelés" === $workType) {

                    // Ha fizetés típusa készpénz, akkor ki kell tölteni az összeget:
                    if ($paymentMethod === 'cash' && (empty($paymentAmount) || $paymentAmount == 0)) {
                        return response()->json([
                            'message' => 'Készpénzes fizetés esetén a fizetett összeg megadása kötelező.',
                        ], 422);
                    }

                    // Extra adatok közül ezek a kötelezők:

                    $extraData = $request->input('extra_data', []);
                    $requiredExtraFields = [
                        'pipe' => 'Mennyi plusz csövet használtál?',
                        'console'   => 'Milyen konzolt használtál?',
                    ];

                    $missingFields = [];

                    foreach ($requiredExtraFields as $field => $label) {
                        if (empty($extraData[$field])) {
                            $missingFields[] = $label;
                        }
                    }

                    if (!empty($missingFields)) {
                        return response()->json([
                            'message' => 'A "Kész" státuszhoz az alábbi extra mezők is szükségesek: ' . implode(', ', $missingFields)
                        ], 422);
                    }

                    // Ellenőrizni kell a képeket
                    $existingPhotos = collect();
                    $worksheet = $worksheetId ? Worksheet::with('photos')->find($worksheetId) : null;

                    if ($worksheet) {
                        $existingPhotos = $worksheet->photos->groupBy('image_type');
                    }

                    $hasDatatable = $request->hasFile('new_photos_to_datatable') || ($existingPhotos->has('Adattábla') && $existingPhotos['Adattábla']->isNotEmpty());
                    $hasCertificate = $request->hasFile('new_photos_to_certificate') || ($existingPhotos->has('Telepítési tanúsítvány') && $existingPhotos['Telepítési tanúsítvány']->isNotEmpty());
                    $hasInstall = $request->hasFile('new_photos_to_install') || ($existingPhotos->has('Szerelés') && $existingPhotos['Szerelés']->isNotEmpty());

                    if (!$hasDatatable || !$hasCertificate || !$hasInstall) {
                        return response()->json([
                            'message' => 'A "Kész" státuszhoz minden képtípusból legalább 1 kép szükséges (Adattábla, Tanúsítvány, Szerelés).'
                        ], 422);
                    }
                }
                if ("Karbantartás" === $workType) {

                    // Ha fizetés típusa készpénz, akkor ki kell tölteni az összeget:
                    if ($paymentMethod === 'cash' && (empty($paymentAmount) || $paymentAmount == 0)) {
                        return response()->json([
                            'message' => 'Készpénzes fizetés esetén a fizetett összeg megadása kötelező.',
                        ], 422);
                    }

                    // Extra adatok közül ezek a kötelezők:

                    $extraData = $request->input('extra_data', []);
                    $requiredExtraFields = [
                        'cleaning_type' => 'Tisztítás típusa'
                    ];

                    $missingFields = [];

                    foreach ($requiredExtraFields as $field => $label) {
                        if (empty($extraData[$field])) {
                            $missingFields[] = $label;
                        }
                    }

                    if (!empty($missingFields)) {
                        return response()->json([
                            'message' => 'A "Kész" státuszhoz az alábbi extra mezők is szükségesek: ' . implode(', ', $missingFields)
                        ], 422);
                    }
                }

                if ("Felmérés" === $workType) {
                    // Extra adatok közül ezek a kötelezők:

                    $extraData = $request->input('extra_data', []);
                    $requiredExtraFields = [
                        'exist_contract' => 'Szerződéskötés történt?'
                    ];

                    $missingFields = [];

                    foreach ($requiredExtraFields as $field => $label) {
                        if (empty($extraData[$field])) {
                            $missingFields[] = $label;
                        }
                    }

                    if (!empty($missingFields)) {
                        return response()->json([
                            'message' => 'A "Kész" státuszhoz az alábbi extra mezők is szükségesek: ' . implode(', ', $missingFields)
                        ], 422);
                    }
                }

            }


            if ($worksheetId) {
                $worksheet = Worksheet::findOrFail($worksheetId);

                // Ha "Lezárva" státuszba mentik (csak akkor, ha már "Kész" állapotban van)
                if ($request->input('work_status') === 'Lezárva' && $worksheet->work_status == "Folyamatban") {
                    return response()->json([
                        'message' => 'Csak "Kész" állapotban lévő munkalap zárható le',
                    ], 422);
                }

                // További kritériumok, hogy lezárva állapotba kerüljön
                if ($request->input('work_status') === 'Lezárva' && $worksheet->work_status == "Kész") {
                    if ("Szerelés" === $request->input('work_type')) {

                        // Ellenőrizni kell a számlát
                        $existingBilling = collect();
                        $worksheet = $worksheetId ? Worksheet::with('photos')->find($worksheetId) : null;

                        if ($worksheet) {
                            $existingBilling = $worksheet->photos->groupBy('image_type');
                        }

                        $hasBilling = $request->hasFile('new_billings') || ($existingBilling->has('Számla') && $existingBilling['Számla']->isNotEmpty());

                        if (!$hasBilling) {
                            return response()->json([
                                'message' => 'A "Lezárva" státuszhoz fel kell tölteni számlát.'
                            ], 422);
                        }
                    }
                }

                $worksheet->update($data);
                $worksheet->products()->detach(); // előző termékek törlése
            } else {
                if ($request->input('work_status') === "Folyamatban") {
                    $worksheet = Worksheet::create($data + [
                            'created_by' => auth('admin')->id()
                        ]);
                } else {
                    return response()->json([
                        'message' => 'Csak "Folyamatban" állapottal hozható létre munkalap',
                    ], 422);
                }
            }

            // Termékek mentése
            foreach ($request->input('products', []) as $productId => $data) {
                if (!isset($data['selected'])) continue;

                WorksheetProduct::create([
                    'worksheet_id' => $worksheet->id,
                    'product_id'   => $productId,
                    'quantity'     => $data['qty'],
                ]);
            }

            // Képek és fájlok mentése
            $imageGroups = [
                'new_photos_to_local' => 'Helyszíni felmérés',
                'new_photos_to_datatable' => 'Adattábla',
                'new_photos_to_certificate' => 'Telepítési tanúsítvány',
                'new_photos_to_install' => 'Szerelés',
                'new_billings' => 'Számla',
            ];

            foreach ($imageGroups as $inputName => $imageType) {
                if ($request->hasFile($inputName)) {
                    $photos = [];

                    foreach ($request->file($inputName) as $file) {
                        $extension = strtolower($file->getClientOriginalExtension());
                        $filename = Str::random(40) . '.' . $extension;
                        $storagePath = 'worksheet_images/' . $filename;

                        if (!Storage::disk('local')->exists($storagePath)) {
                            // Mentés a storage/app/worksheet_images alá
                            $file->storeAs('worksheet_images', $filename, 'local');

                            // Teljes fájlút
                            $fullPath = Storage::disk('local')->path($storagePath);

                            // Csak képek optimalizálása (ne pdf/doc)
                            if (!in_array($extension, ['pdf', 'doc', 'docx'])) {
                                ImageOptimizer::optimize($fullPath);
                            }

                            $photos[] = [
                                'image_path' => $filename,
                                'image_type' => $imageType,
                            ];
                        }
                    }

                    if (!empty($photos)) {
                        $worksheet->photos()->createMany($photos);
                    }
                }
            }



            DB::commit();

            return response()->json([
                'message' => $worksheetId ? 'Sikeres frissítés!' : 'Sikeres mentés!',
                'id' => $worksheet->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Munkalap mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchWithCategories() {
        $categories = Category::whereHas('products', function($query) {
            $query->where('is_selectable_by_installer', 1);
        })
            ->with(['products' => function($query) {
                $query->where('is_selectable_by_installer', 1);
            }])
            ->orderBy('title')
            ->get();

        return response()->json($categories);
    }

    public function showDataToWorksheet($id)
    {
        $worksheet = Worksheet::with(['products', 'photos'])->findOrFail($id);

        // Átalakítjuk a kapcsolódó product adatokat, hogy pivot helyett a quantity direktben legyen benne
        $worksheet->products = $worksheet->products->map(function ($product) {
            $product->quantity = $product->pivot->quantity;
            unset($product->pivot); // opcionális: eltüntetjük a pivotot teljesen
            return $product;
        });

        return response()->json($worksheet);
    }

    public function deleteWorksheetPhoto(Request $request) {
        try {
            $worksheet_photo = WorksheetImage::findOrFail($request->id);

            Storage::disk('local')->delete('worksheet_images/' . $worksheet_photo->image_path);
            $worksheet_photo->delete();


            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Munkalap kép törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        $worksheet = Worksheet::findOrFail($request->id);

        try {
            // Először töröljük a munkalaphoz tartozó képeket
            foreach ($worksheet->photos as $photo) {
                Storage::disk('local')->delete('worksheet_images/' . $photo->image_path);
                $photo->delete();
            }

            // Majd töröljük magát a munkalapot
            $worksheet->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Munkalap törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
