<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewOffer;
use App\Models\Category;
use App\Models\Offer;
use App\Models\OfferProduct;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OfferController extends Controller
{
    public function index()
    {
        return view('admin.business.offers');
    }

    public function previewPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Hibás adatok.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $offer = new Offer([
                'title' => $request->input('title'),
                'name' => $request->input('contact_name'),
                'country' => $request->input('contact_country'),
                'zip_code' => $request->input('contact_zip_code'),
                'city' => $request->input('contact_city'),
                'address_line' => $request->input('contact_address_line'),
                'phone' => $request->input('contact_phone'),
                'email' => $request->input('contact_email'),
                'description' => $request->input('contact_description'),
            ]);

            $productsInput = (array) $request->input('products', []);
            $selectedProductIds = [];
            foreach ($productsInput as $productId => $data) {
                if (isset($data['selected'])) {
                    $selectedProductIds[] = (int) $productId;
                }
            }

            $titlesById = Product::whereIn('id', $selectedProductIds)
                ->pluck('title', 'id')
                ->toArray();

            $products = [];
            foreach ($productsInput as $productId => $data) {
                if (!isset($data['selected'])) {
                    continue;
                }

                $quantity = (int) ($data['quantity'] ?? 1);
                $grossPrice = (float) ($data['gross_price'] ?? 0);
                $products[] = [
                    'title' => $titlesById[(int) $productId] ?? 'N/A',
                    'quantity' => $quantity > 0 ? $quantity : 1,
                    'gross_price' => $grossPrice,
                ];
            }

            $data = [
                'offer' => $offer,
                'products' => $products,
            ];

            $pdf = Pdf::loadView('pdf.offer_20250613', $data);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="ajanlat_elonezet.pdf"',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Ajánlat PDF előnézet hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a PDF előnézet generálása során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function data()
    {
        $offers = Offer::select([
            'offers.id',
            'offers.title',
            'offers.name',
            'offers.country',
            'offers.zip_code',
            'offers.city',
            'offers.address_line',
            'offers.created_at as created',
            'users.name as creator_name',   // 👉 innen jön a creator neve
            'offers.viewed_by',
            'offers.viewed_at',
        ])
            ->leftJoin('users', 'offers.created_by', '=', 'users.id');

        return DataTables::of($offers)
            ->editColumn('created_at', function ($offer) {
                return $offer->created_at
                    ? \Carbon\Carbon::parse($offer->created_at)->format('Y-m-d H:i:s')
                    : '';
            })
            ->addColumn('creator_name', function ($offer) {
                return $offer->creator_name ?? 'Ismeretlen';
            })
            ->addColumn('viewed_by', function ($offer) {
                if ($offer->viewed_by) {
                    return '<span title="Megtekintve: '
                        . ($offer->viewed_at ? \Carbon\Carbon::parse($offer->viewed_at)->format('Y-m-d H:i:s') : '-')
                        . '">' . e($offer->viewed_by) . '</span>';
                }
                return '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->addColumn('action', function ($offer) {
                return '
                <button class="btn btn-sm btn-primary view" data-id="'.$offer->id.'" title="Megtekintés">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-danger delete" data-id="'.$offer->id.'" title="Törlés">
                    <i class="fas fa-trash-alt"></i>
                </button>
            ';
            })
            ->setRowClass(function ($offer) {
                return $offer->viewed_by ? '' : 'fw-bold';
            })
            ->rawColumns(['action', 'viewed_by'])
            ->make(true);
    }


    public function showProductsToOffer($id)
    {
        $offer = Offer::with(['products'])->findOrFail($id);

        return response()->json([
            'offer' => $offer
        ]);
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'products' => 'required|array',
            'products.*.gross_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $offer = Offer::create([
                'title' => $request->input('title'),
                'name' => $request->input('contact_name'),
                'country' => $request->input('contact_country'),
                'zip_code' => $request->input('contact_zip_code'),
                'city' => $request->input('contact_city'),
                'address_line' => $request->input('contact_address_line'),
                'phone' => $request->input('contact_phone'),
                'email' => $request->input('contact_email'),
                'description' => $request->input('contact_description'),
                'created_by' => auth('admin')->id(),
            ]);

            $products = [];

            foreach ($request->input('products') as $productId => $data) {
                if (!isset($data['selected'])) {
                    continue;
                }

                $item = OfferProduct::create([
                    'offer_id' => $offer->id,
                    'product_id' => $productId,
                    'quantity' => $data['quantity'] ?? 1,
                    'gross_price' => $data['gross_price'],
                ]);
                $products[] = [
                    'title' => Product::findOrFail($productId)->title ?? "N/A",
                    'quantity' => $item->quantity,
                    'gross_price' => $item->gross_price,
                ];
            }


            $data = [
                'offer' => $offer,
                'products' => $products,
                // TODO: 'company' => config('app.company_info')
            ];

            // PDF generálása
            $pdf = Pdf::loadView('pdf.offer_20250613', $data);

            $fileName = 'offer_' . $offer->id . '.pdf';
            Storage::put("offers/{$fileName}", $pdf->output());

            $offer->update(['pdf_path' => "offers/{$fileName}"]);

            DB::commit();

            // E-mail küldése az ajánlatról
            if ($offer->email) {
                Mail::to($offer->email)->send(new NewOffer($offer));
            }

            return response()->json([
                'message' => 'Sikeres generálás!'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Ajánlat mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $offer = Offer::findOrFail($request->id);

        try {
            // Töröljük a PDF fájlt, ha létezik
            if ($offer->pdf_path && Storage::exists($offer->pdf_path)) {
                Storage::delete($offer->pdf_path);
            }

            // Töröljük az ajánlatot és a hozzá tartozó termékeket
            $offer->offerProducts()->delete();
            $offer->delete();

            return response()->json([
                'message' => 'Sikeres törlés!'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ajánlat törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

}
