<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contract;
use App\Models\ContractProduct;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('admin.business.contracts', [
            'versions' => $versions,
        ]);
    }

    public function data()
    {
        $contracts = Contract::select([
            'contracts.id',
            'contracts.name',
            'contracts.country',
            'contracts.zip_code',
            'contracts.city',
            'contracts.address_line',
            'contracts.installation_date',
            'contracts.created_at as created',
            'users.name as creator_name'])
            ->leftJoin('users', 'contracts.created_by', '=', 'users.id');

        return DataTables::of($contracts)
            ->addColumn('creator_name', function ($contract) {
                return $contract->creator_name ?? 'Ismeretlen';
            })
            ->addColumn('action', function ($contract) {
                return '
                    <button class="btn btn-sm btn-primary view" data-id="'.$contract->id.'" title="Megtekintés">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$contract->id.'" title="Törlés">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
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

        DB::beginTransaction();

        try {
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

            $contract = Contract::create([
                'version' => $request->input('contract_version'),
                'name' => $request->input('contact_name'),
                'country' => $request->input('contact_country'),
                'zip_code' => $request->input('contact_zip_code'),
                'city' => $request->input('contact_city'),
                'address_line' => $request->input('contact_address_line'),
                'installation_date' => $request->input('installation_date'),
                'phone' => $request->input('contact_phone'),
                'email' => $request->input('contact_email'),
                'mothers_name' => $request->input('mothers_name'),
                'place_of_birth' => $request->input('place_of_birth'),
                'date_of_birth' => $request->input('date_of_birth'),
                'id_number' => $request->input('id_number'),
                'data' => $request->input('contract_data', []),
                'signature_path' => "{$signatureName}",
                'created_by' => auth('admin')->id(),
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
                ];
            }

            $pdf_data = [
                'contract' => $contract->toArray(),
                'products' => $products,
                'data' => $contract->data,
                'signature_path' => $signatureName ? storage_path("app/private/signatures/{$signatureName}") : null,
                // 'company' => config('app.company_info') // opcionális
            ];

            \Log::info($pdf_data);

            // PDF generálása
            $pdf = Pdf::loadView('pdf.contract_' . $request->get('contract_version'), $pdf_data);

            $file_name = 'contract_' . $contract->id . '.pdf';
            Storage::put("contracts/{$file_name}", $pdf->output());

            $contract->update(['pdf_path' => "contracts/{$file_name}"]);

            DB::commit();

            return response()->json([
                'message' => 'Sikeres generálás!'
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
}
