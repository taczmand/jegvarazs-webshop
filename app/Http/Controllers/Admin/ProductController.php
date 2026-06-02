<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PartnerProduct;
use App\Models\Product;
use App\Models\ProductQuantityDiscount;
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

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $customerId = $request->query('customer_id');
        $showPartnerPrices = $request->boolean('show_partner_prices');
        if ($q === '') {
            return response()->json(['products' => []]);
        }

        $customer = null;
        if ($customerId !== null && $customerId !== '' && is_numeric($customerId)) {
            $customer = Customer::query()->find((int) $customerId);
        }

        $products = Product::query()
            ->with('taxCategory')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('id', '=', is_numeric($q) ? (int) $q : 0);
            })
            ->orderBy('title')
            ->limit(20)
            ->get(['id', 'title', 'gross_price', 'partner_gross_price', 'tax_id']);

        $partnerDiscounts = collect();
        if ($customer && $customer->is_partner && $products->isNotEmpty()) {
            $partnerDiscounts = PartnerProduct::query()
                ->where('customer_id', $customer->id)
                ->whereIn('product_id', $products->pluck('id')->all())
                ->pluck('discount_gross_price', 'product_id');
        }

        $payload = $products->map(function ($p) use ($customer, $partnerDiscounts, $showPartnerPrices) {
            $effective = (float) ($p->gross_price ?? 0);
            if ($customer && $customer->is_partner) {
                $disc = $partnerDiscounts->get($p->id);
                if ($disc !== null) {
                    $effective = (float) $disc;
                } elseif ($p->partner_gross_price !== null) {
                    $effective = (float) $p->partner_gross_price;
                }
            } elseif (!$customer && $showPartnerPrices && $p->partner_gross_price !== null) {
                $effective = (float) $p->partner_gross_price;
            }

            return [
                'id' => $p->id,
                'title' => $p->title,
                'gross_price' => $p->gross_price,
                'partner_gross_price' => $p->partner_gross_price,
                'effective_gross_price' => $effective,
                'tax_value' => $p->taxCategory?->tax_value,
            ];
        })->values();

        return response()->json(['products' => $payload]);
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
            ])
            ->addSelect([
                'in_cart_count' => CartItem::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('cart_items.product_id', 'products.id'),
                'main_photo_path' => ProductPhoto::query()
                    ->select('path')
                    ->whereColumn('product_photos.product_id', 'products.id')
                    ->orderByDesc('is_main')
                    ->limit(1),
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
            ->addColumn('photo_url', function ($product) {
                $path = $product->main_photo_path;
                if ($path) {
                    return asset('storage/' . ltrim($path, '/'));
                }
                return asset('static_media/no-image.jpg');
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
            ->addColumn('in_cart', function ($product) {
                return (int) ($product->in_cart_count ?? 0) > 0;
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

    public function quantityDiscounts($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $product = Product::query()->findOrFail($id);

        $discounts = ProductQuantityDiscount::query()
            ->where('product_id', $product->id)
            ->orderBy('min_quantity')
            ->get();

        return response()->json([
            'product_id' => $product->id,
            'discounts' => $discounts,
        ]);
    }

    public function storeQuantityDiscount(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $product = Product::query()->findOrFail($id);

        $data = $request->validate([
            'min_quantity' => 'required|integer|min:1',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['product_id'] = $product->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $discount = ProductQuantityDiscount::create($data);

        return response()->json([
            'message' => 'Kedvezmény mentve.',
            'discount' => $discount,
        ]);
    }

    public function updateQuantityDiscount(Request $request, ProductQuantityDiscount $discount)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $data = $request->validate([
            'min_quantity' => 'required|integer|min:1',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $discount->update($data);

        return response()->json([
            'message' => 'Kedvezmény frissítve.',
            'discount' => $discount->fresh(),
        ]);
    }

    public function destroyQuantityDiscount(ProductQuantityDiscount $discount)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $discount->delete();

        return response()->json([
            'message' => 'Kedvezmény törölve.',
        ]);
    }


    /**
     * AJAX endpoint: adott termék adatainak lekérése JSON-ként.
     */
    public function show($id)
    {
        try {
            $product = Product::query()->findOrFail($id);

            $productData = $this->product_service->getProductWithPivotMeta($id);
            if (!$productData) {
                return response()->json(['message' => 'Termék nem található'], 404);
            }

            $discounts = ProductQuantityDiscount::query()
                ->where('product_id', $product->id)
                ->orderBy('min_quantity')
                ->get();

            $original = $productData->getData(true);
            $original['quantity_discounts'] = $discounts;

            return response()->json($original);
        } catch (\Exception $e) {
            \Log::error('Termék mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a termék lekérdezése közben.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function historyData($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product')) {
            return response()->json(['message' => 'Nincs jogosultságod a termék történetének megtekintéséhez.'], 403);
        }

        $productId = (int) $id;
        if ($productId <= 0) {
            return response()->json(['message' => 'Érvénytelen termék azonosító.'], 422);
        }

        $items = \DB::table('user_actions')
            ->leftJoin('users', 'user_actions.user_id', '=', 'users.id')
            ->where('user_actions.model', '=', 'products')
            ->where('user_actions.model_id', '=', $productId)
            ->select([
                'user_actions.id',
                'users.name as user_name',
                'user_actions.action',
                'user_actions.data',
                'user_actions.created_at',
            ])
            ->orderBy('user_actions.created_at', 'desc');

        return DataTables::of($items)
            ->editColumn('action', function ($row) {
                $map = [
                    'created' => 'Létrehozott',
                    'updated' => 'Frissített',
                    'deleted' => 'Törölt',
                ];
                $a = (string) ($row->action ?? '');
                return $map[$a] ?? $a;
            })
            ->editColumn('data', function ($row) {
                $data = $row->data;
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }

                if (!is_array($data)) {
                    return $data;
                }

                if (!array_key_exists('old', $data) && !array_key_exists('new', $data)) {
                    $data = [
                        'new' => $data,
                    ];
                }

                $old = isset($data['old']) && is_array($data['old']) ? $data['old'] : [];
                $new = isset($data['new']) && is_array($data['new']) ? $data['new'] : [];

                $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                sort($keys);

                $changes = [];
                foreach ($keys as $k) {
                    if ($k === '_record') {
                        continue;
                    }
                    $before = $old[$k] ?? null;
                    $after = $new[$k] ?? null;
                    if ($before === $after) {
                        continue;
                    }
                    $changes[] = [
                        'field' => (string) $k,
                        'old' => $before,
                        'new' => $after,
                    ];
                }

                return [
                    'old' => $old,
                    'new' => $new,
                    'changes' => $changes,
                ];
            })
            ->make(true);
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

    public function setProductInline(Request $request) {
        try {
            $product = Product::findOrFail($request->id);

            $old_gross_price = (float) ($product->gross_price ?? 0);
            $product->update([
                $request->field => $request->value,
            ]);

            if ((string) $request->field === 'gross_price') {
                $new_gross_price = (float) ($product->fresh()->gross_price ?? 0);
                if ($new_gross_price !== $old_gross_price) {
                    $this->product_service->sync_partner_discount_prices($product->fresh());
                }
            }

            return response()->json([
                'message' => 'Sikeres módosítás!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Termék módosítási hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a módosítás során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
