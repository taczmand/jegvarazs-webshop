<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Services\Admin\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    protected $product_service;

    public function __construct(ProductService $service)
    {
        $this->product_service = $service;
    }
    public function index()
    {
        $categories = Category::orderBy('title')->get();
        return view('admin.products.products', compact('categories'));
    }

    public function data()
    {
        $products = Product::with(['category', 'taxCategory'])
            ->select([
                'id',
                'title',
                'stock',
                'gross_price',
                'partner_gross_price',
                'status',
                'created_at',
                'cat_id',
                'tax_id'
            ]);

        return DataTables::of($products)
            // ID szűrés: pontos egyezés
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('products.id', $keyword);
                }
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('id', '=', "{$keyword}");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', '=', "{$keyword}");
            })
            ->addColumn('category', function ($product) {
                return $product->category ? $product->category->title : '';
            })
            ->editColumn('gross_price', function ($product) {
                return number_format($product->gross_price, 0, ',', ' ') . ' Ft';
            })
            ->editColumn('partner_gross_price', function ($product) {
                return number_format($product->partner_gross_price, 0, ',', ' ') . ' Ft';
            })
            ->addColumn('tax_value', function ($product) {
                return $product->taxCategory?->tax_value . '%' ?? '';
            })
            ->editColumn('created_at', function ($product) {
                return $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($product) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-product')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $product->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-product')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $product->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    /**
     * AJAX endpoint: adott termék adatainak lekérése JSON-ként.
     */
    public function show($id)
    {
        try {
            $productData = $this->product_service->getProductWithPivotMeta($id);

            if (!$productData) {
                return response()->json(['message' => 'Termék nem található'], 404);
            }

            return response()->json($productData);
        } catch (\Exception $e) {
            \Log::error('Termék mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a termék lekérdezése közben.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function meta()
    {
        $all_meta = $this->product_service->getAllMeta();

        if (!$all_meta) {
            return response()->json(['message' => 'Nem található meta'], 404);
        }

        return response()->json($all_meta);
    }

    public function store(ProductRequest $request)
    {
        try {
            $product = $this->product_service->store($request->all());

            return response()->json([
                'message' => 'Sikeres mentés!',
                'product' => $product,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ProductRequest $request)
    {
        try {
            $product = $this->product_service->update($request->all());

            return response()->json([
                'message' => 'Sikeres mentés!',
                'product' => $product,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProductPhotoAlt(Request $request) {
        try {
            $product_photo = ProductPhoto::findOrFail($request->id);
            $product_photo->alt = $request->alt;
            $product_photo->save();

            return response()->json([
                'message' => 'Sikeres mentés!',
                'product_photo' => $product_photo,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék kép alt mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }

    public function setPrimaryProductPhoto(Request $request) {
        try {
            $product_photo = ProductPhoto::findOrFail($request->id);
            $product = $product_photo->product;

            // Először minden képet nem primary-re állítunk
            $product->photos()->update(['is_main' => false]);

            // Majd az aktuális képet primary-re állítjuk
            $product_photo->is_main = true;
            $product_photo->save();

            return response()->json([
                'message' => 'Sikeres mentés!',
                'product_photo' => $product_photo,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék fő kép mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }

    public function deleteProductPhoto(Request $request) {
        try {
            $product_photo = ProductPhoto::findOrFail($request->id);

            Storage::disk('public')->delete($product_photo->path);
            $product_photo->delete();


            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék kép törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        $product = Product::findOrFail($request->id);

        try {
            // Először töröljük a termékhez tartozó képeket
            foreach ($product->photos as $photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }

            // Majd töröljük magát a terméket
            $product->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }

    public function fetchWithCategories() {
        $categories = Category::with(['products'])
            ->orderBy('title')
            ->get();

        return response()->json($categories);
    }

    public function uploadProductPhotos(Request $request) {
        try {
            $uploaded_photos = $this->product_service->uploadProductPhotos($request->all());

            return response()->json([
                'message' => 'Sikeres feltöltés!',
                'photos' => $uploaded_photos,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék kép feltöltési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a feltöltés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
