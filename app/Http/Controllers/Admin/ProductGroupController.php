<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductGroupQuantityDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductGroupController extends Controller
{
    public function index()
    {
        return view('admin.products.product-groups');
    }

    public function data(Request $request)
    {
        $groups = ProductGroup::query()
            ->select([
                'product_groups.id as id',
                'product_groups.name as name',
                'product_groups.slug as slug',
                'product_groups.is_active as is_active',
                'product_groups.created_at as created',
                'product_groups.updated_at as updated',
                'pgqd.id as quantity_discount_id',
            ])
            ->leftJoin('product_group_quantity_discounts as pgqd', 'product_groups.id', '=', 'pgqd.product_group_id');

        $discountFilter = (string) ($request->get('quantity_discount') ?? '');
        if ($discountFilter === 'with') {
            $groups->whereNotNull('pgqd.id');
        }
        if ($discountFilter === 'without') {
            $groups->whereNull('pgqd.id');
        }

        return DataTables::of($groups)
            ->addColumn('status', function ($row) {
                return (bool) $row->is_active ? 'Aktív' : 'Inaktív';
            })
            ->addColumn('action', function ($group) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-product-group')) {
                    $buttons .= '<button class="btn btn-sm btn-primary edit" data-id="' . $group->id . '" title="Szerkesztés"><i class="fas fa-edit"></i></button>';

                    $qdBtnClass = $group->quantity_discount_id ? 'btn-success' : 'btn-info';
                    $buttons .= '<button class="btn btn-sm ' . $qdBtnClass . ' ms-1 quantity-discount" data-id="' . $group->id . '" title="Mennyiségi kedvezmény"><i class="fas fa-percent"></i></button>';

                    $buttons .= '<button class="btn btn-sm btn-secondary ms-1 manage-products" data-id="' . $group->id . '" title="Termékek"><i class="fas fa-box"></i></button>';
                }

                if ($user && $user->can('delete-product-group')) {
                    $buttons .= '<button class="btn btn-sm btn-danger ms-1 delete" data-id="' . $group->id . '" title="Törlés"><i class="fas fa-trash"></i></button>';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-product-groups')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        return response()->json($group);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $group = ProductGroup::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return response()->json(['message' => 'Sikeres mentés!', 'group' => $group]);
    }

    public function update(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $group->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return response()->json(['message' => 'Sikeres mentés!', 'group' => $group->fresh()]);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);
        $group->delete();

        return response()->json(['message' => 'Sikeres törlés!']);
    }

    public function quantityDiscount($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $discount = ProductGroupQuantityDiscount::query()
            ->where('product_group_id', $group->id)
            ->first();

        return response()->json(['product_group_id' => $group->id, 'discount' => $discount]);
    }

    public function upsertQuantityDiscount(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        if ($request->filled('starts_at')) {
            $request->merge(['starts_at' => str_replace('T', ' ', (string) $request->input('starts_at'))]);
        }
        if ($request->filled('ends_at')) {
            $request->merge(['ends_at' => str_replace('T', ' ', (string) $request->input('ends_at'))]);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $data = $request->validate([
            'base_quantity' => 'required|integer|min:1',
            'percent_per_step' => 'required|numeric|min:0',
            'max_percent' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['product_group_id'] = $group->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $discount = ProductGroupQuantityDiscount::query()
            ->updateOrCreate(['product_group_id' => $group->id], $data);

        return response()->json(['message' => 'Kedvezmény mentve.', 'discount' => $discount->fresh()]);
    }

    public function destroyQuantityDiscount($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        ProductGroupQuantityDiscount::query()
            ->where('product_group_id', $group->id)
            ->delete();

        return response()->json(['message' => 'Kedvezmény törölve.']);
    }

    public function products($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);
        $productIds = $group->products()->pluck('products.id')->all();

        return response()->json(['product_group_id' => $group->id, 'product_ids' => $productIds]);
    }

    public function productsCatalog($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);
        $selectedProductIds = $group->products()->pluck('products.id')->all();

        $categories = Category::query()
            ->select(['categories.id', 'categories.title'])
            ->orderBy('categories.title')
            ->get();

        $products = Product::query()
            ->select(['products.id', 'products.title', 'products.cat_id', 'products.status'])
            ->orderBy('products.title')
            ->get()
            ->groupBy('cat_id');

        $payload = $categories->map(function ($cat) use ($products) {
            $items = ($products[(int) $cat->id] ?? collect())->values()->map(function ($p) {
                return [
                    'id' => (int) $p->id,
                    'title' => (string) $p->title,
                    'status' => (string) $p->status,
                ];
            });

            return [
                'id' => (int) $cat->id,
                'title' => (string) $cat->title,
                'products' => $items,
            ];
        })->values();

        return response()->json([
            'product_group_id' => $group->id,
            'selected_product_ids' => array_map('intval', $selectedProductIds),
            'categories' => $payload,
        ]);
    }

    public function addProducts(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $group->products()->syncWithoutDetaching($data['product_ids']);

        return response()->json(['message' => 'Termékek hozzáadva.']);
    }

    public function syncProducts(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $data = $request->validate([
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['product_ids'] ?? [])));
        $group->products()->sync($ids);

        return response()->json(['message' => 'Termékcsoport termékei frissítve.']);
    }

    public function addCategory(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $category = Category::query()->findOrFail((int) $data['category_id']);

        $productIds = Product::query()
            ->where('cat_id', $category->id)
            ->pluck('id')
            ->all();

        if (count($productIds) === 0) {
            return response()->json(['message' => 'A kategóriában nincs termék.']);
        }

        $group->products()->syncWithoutDetaching($productIds);

        return response()->json(['message' => 'Kategória termékei hozzáadva.', 'count' => count($productIds)]);
    }

    public function removeProducts(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-product-group')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $group = ProductGroup::query()->findOrFail($id);

        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $group->products()->detach($data['product_ids']);

        return response()->json(['message' => 'Termékek eltávolítva.']);
    }
}
