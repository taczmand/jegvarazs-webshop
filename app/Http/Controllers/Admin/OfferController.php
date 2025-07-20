<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\OfferProduct;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class OfferController extends Controller
{
    public function index()
    {
        return view('admin.business.offers');
    }

    public function data()
    {
        $offers = Offer::select([
            'offers.id',
            'offers.name',
            'offers.country',
            'offers.zip_code',
            'offers.city',
            'offers.address_line',
            'offers.created_at as created',
            'users.name as creator_name'])
            ->leftJoin('users', 'offers.created_by', '=', 'users.id');

        return DataTables::of($offers)
            ->addColumn('creator_name', function ($offer) {
                return $offer->creator_name ?? 'Ismeretlen';
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
            ->rawColumns(['action'])
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
        /*$validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'products' => 'required|array',
            'products.*.gross_price' => 'nullable|numeric|min:0',
        ]);*/

        DB::beginTransaction();

        try {
            $offer = Offer::create([
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
                    'gross_price' => $data['gross_price'],
                ]);
                $products[] = [
                    'title' => Product::findOrFail($productId)->title ?? "N/A",
                    'gross_price' => $item->gross_price,
                ];
            }


            $data = [
                'offer' => $offer,
                'products' => $products,
                // TODO: 'company' => config('app.company_info')
            ];

            // PDF generálása
            $pdf = Pdf::loadView('pdf.offer_20250720', $data);

            $fileName = 'offer_' . $offer->id . '.pdf';
            Storage::put("offers/{$fileName}", $pdf->output());

            $offer->update(['pdf_path' => "offers/{$fileName}"]);

            DB::commit();

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
            $offer->products()->delete();
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
