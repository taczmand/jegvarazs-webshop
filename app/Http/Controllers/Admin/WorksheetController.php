<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\Worksheet;
use App\Models\User;
use App\Models\Contract;
use App\Models\WorksheetImage;
use App\Models\WorksheetProduct;
use App\Models\WorksheetWorker;
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

    public function getDataToCalendarByWeek(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Missing date range'], 400);
        }

        $user = auth('admin')->user();

        // WORKSHEETS
        if ($user->can('view-worksheets')) {
            $query = Worksheet::with(['workers:id,name'])
                ->whereBetween('installation_date', [$startDate, $endDate])
                ->orderBy('sort_order', 'ASC');
        } elseif ($user->can('view-own-worksheets')) {
            $query = Worksheet::with(['workers:id,name'])
                ->whereHas('workers', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->whereBetween('installation_date', [$startDate, $endDate])
                ->orderBy('sort_order', 'ASC');
        } else {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $worksheets = $query->get();

        // mindig legyen kollekció
        $result = collect();

        if ($worksheets->isNotEmpty()) {
            $result = $worksheets->map(function ($worksheet) {
                return [
                    'id' => $worksheet->id,
                    'name' => $worksheet->name,
                    'city' => $worksheet->city,
                    'work_name' => $worksheet->work_name,
                    'work_status' => $worksheet->work_status,
                    'installation_date' => $worksheet->installation_date,
                    'worker_name' => $worksheet->workers->pluck('name')->implode(', '),
                    'model' => 'worksheet',
                    'type' => $worksheet->work_type,
                    'sort_order' => $worksheet->sort_order,
                ];
            });
        }

        // APPOINTMENTS
        if ($user->can('view-appointments')) {
            $appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                ->orderBy('sort_order', 'ASC')
                ->get();

            $appointment_results = $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'name' => $appointment->name,
                    'city' => $appointment->city,
                    'work_name' => null,
                    'work_status' => $appointment->status,
                    'installation_date' => $appointment->appointment_date,
                    'worker_name' => null,
                    'model' => 'appointment',
                    'type' => $appointment->appointment_type,
                    'sort_order' => $appointment->sort_order,
                ];
            });

            $result = $result->merge($appointment_results)
                ->sortBy('sort_order')
                ->values();
        }

        return response()->json($result);
    }

    public function data()
    {
        $user = $this->user;

        $worksheetsQuery = Worksheet::select([
            'worksheets.id',
            'worksheets.installation_date',
            'worksheets.name',
            'worksheets.city',
            'worksheets.work_type',
            'worksheets.work_status',
            'worksheets.data',
            'worksheets.contract_id',
            'worksheets.created_at as created',
            'worksheets.viewed_by',
            'worksheets.viewed_at',
            'creator.name as creator_name',
            DB::raw('GROUP_CONCAT(DISTINCT worker.name ORDER BY worker.name SEPARATOR ", ") as worker_name')
        ])
            ->leftJoin('users as creator', 'worksheets.created_by', '=', 'creator.id')
            ->leftJoin('worksheet_workers', 'worksheets.id', '=', 'worksheet_workers.worksheet_id')
            ->leftJoin('users as worker', 'worksheet_workers.worker_id', '=', 'worker.id');

        if ($user->can('view-own-worksheets') && !$user->can('view-worksheets')) {
            $worksheetsQuery->where('worksheet_workers.worker_id', $user->id);
        }

        $worksheetsQuery->groupBy(
            'worksheets.id',
            'worksheets.installation_date',
            'worksheets.name',
            'worksheets.city',
            'worksheets.work_type',
            'worksheets.work_status',
            'worksheets.data',
            'worksheets.contract_id',
            'worksheets.created_at',
            'worksheets.viewed_by',
            'worksheets.viewed_at',
            'creator.name'
        );

        return DataTables::of($worksheetsQuery)
            ->editColumn('created_at', fn($worksheet) => $worksheet->created_at ? \Carbon\Carbon::parse($worksheet->created_at)->format('Y-m-d H:i:s') : '')
            ->editColumn('installation_date', fn($worksheet) => $worksheet->installation_date ? \Carbon\Carbon::parse($worksheet->installation_date)->format('Y-m-d') : '')
            ->addColumn('data', function ($worksheet) {
                $data = $worksheet->data;
                $json = is_string($data) ? json_decode($data, true) : (is_array($data) ? $data : []);

                if (empty($json)) {
                    return '-';
                }

                $html = '';
                foreach ($json as $key => $value) {
                    [$label, $translatedValue] = $this->translateDataEntry($key, $value);
                    $html .= "<div><strong>{$label}:</strong> {$translatedValue}</div>";
                }

                return $html;
            })
            ->addColumn('contract_id', function ($worksheet) {
                if ($worksheet->contract_id) {
                    return '<a class="btn btn-sm btn-info" target="_blank" href="' .
                        route('admin.contracts.index', ['modal' => true, 'id' => $worksheet->contract_id]) .
                        '"><i class="fa-solid fa-link"></i></a>';
                }
                return "-";
            })
            ->addColumn('creator_name', fn($worksheet) => $worksheet->creator_name ?? '-')
            ->addColumn('worker_name', fn($worksheet) => $worksheet->worker_name ?? '-')
            ->addColumn('viewed_by', function ($worksheet) {
                if ($worksheet->viewed_by) {
                    return '<span title="Megtekintve: '
                        . ($worksheet->viewed_at ? \Carbon\Carbon::parse($worksheet->viewed_at)->format('Y-m-d H:i:s') : '-')
                        . '">' . e($worksheet->viewed_by) . '</span>';
                }
                return '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->addColumn('action', function ($worksheet) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-worksheet')) {
                    $buttons .= '<button class="btn btn-sm btn-primary edit" data-id="' . $worksheet->id . '" title="Szerkesztés"><i class="fas fa-edit"></i></button>';
                }
                if ($user && $user->can('delete-worksheet')) {
                    $buttons .= '<button class="btn btn-sm btn-danger delete" data-id="' . $worksheet->id . '" title="Törlés"><i class="fas fa-trash"></i></button>';
                }

                return $buttons;
            })
            ->setRowClass(fn($worksheet) => $worksheet->viewed_by ? '' : 'fw-bold')
            ->rawColumns(['contract_id', 'action', 'data', 'viewed_by'])
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

                $worksheet->workers()->detach(); // előző dolgozók törlése
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

            // Munkalap munkások hozzárendelése
            foreach ($request->input('workers', []) as $workerId => $data) {
                if (!isset($data['selected'])) continue;

                WorksheetWorker::create([
                    'worksheet_id' => $worksheet->id,
                    'worker_id'   => $workerId
                ]);
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
                                try {
                                    $imagick = new \Imagick($fullPath);

                                    // Tömörítési beállítások
                                    if (in_array($extension, ['jpg', 'jpeg'])) {
                                        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                                        $imagick->setImageCompressionQuality(75);
                                    } elseif ($extension === 'png') {
                                        $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                                        $imagick->setImageCompressionQuality(75);
                                    }

                                    // Metaadatok törlése
                                    $imagick->stripImage();

                                    // Felülírás optimalizált változattal
                                    $imagick->writeImage($fullPath);
                                    $imagick->destroy();
                                } catch (\Exception $e) {
                                    \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                                }
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
        $worksheet = Worksheet::with(['products', 'photos', 'workers'])->findOrFail($id);

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

    public function updateItemDateAndOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.model' => 'required|in:worksheet,appointment',
            'items.*.date' => 'required|date',
            'items.*.sort_order' => 'required|integer',
        ]);

        try {
            $map = [
                'worksheet' => \App\Models\Worksheet::class,
                'appointment' => \App\Models\Appointment::class,
            ];

            foreach ($request->input('items') as $item) {
                $modelClass = $map[$item['model']] ?? null;
                if (!$modelClass) {
                    continue; // vagy dobj hibát
                }

                $row = $modelClass::findOrFail($item['id']);

                // dátum frissítése
                if ($item['model'] === 'worksheet') {
                    $row->installation_date = $item['date'];
                } else { // appointment
                    $row->appointment_date = $item['date'];
                }

                // sorrend frissítése
                $row->sort_order = $item['sort_order'];

                $row->save();
            }

            return response()->json([
                'message' => 'Sikeres mentés!',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Dátum/sorrend mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


}
