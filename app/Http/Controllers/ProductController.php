<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\WatchedProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Query paraméterek
        $tags = $request->query('tag');
        $brands = $request->query('brand');
        $attributes = $request->query('attribute');
        $sortBy = $request->query('sortBy');
        $itemsPerPage = $request->query('itemsPerPage', 12);

        // Alap query
        $query = Product::with('category')
            ->where('status', 'active');

        $allProductIds = $query->pluck('id');

        // 🔍 Tag szűrés, ha van
        if ($tags) {
            $tagArray = explode('|', $tags);
            $query->whereHas('tags', function ($q) use ($tagArray) {
                $q->whereIn('tag_id', $tagArray);
            });
        }

        // 🔍 Brand szűrés, ha van
        if ($brands) {
            $brandArray = explode('|', $brands);
            $query->whereHas('brands', function ($q) use ($brandArray) {
                $q->whereIn('brand_id', $brandArray);
            });
        }

        // 🔍 Attribútum szűrés, ha van
        if ($attributes) {
            // Pl. ?attribute=3:red,5:xl
            $attributeArray = explode('|', $attributes);

            $query->whereHas('attributes', function ($q) use ($attributeArray) {
                foreach ($attributeArray as $attr) {
                    // "id:value" formátum
                    [$attrId, $value] = explode(':', $attr);
                    $q->where(function ($sub) use ($attrId, $value) {
                        $sub->where('attribute_id', $attrId)
                            ->where('value', $value);
                    });
                }
            });
        }



        // 🔃 Rendezés
        switch ($sortBy) {
            case 'productDesc':
                $query->orderBy('title', 'desc');
                break;
            case 'priceAsc':
                $query->orderBy('gross_price', 'asc');
                break;
            case 'priceDesc':
                $query->orderBy('gross_price', 'desc');
                break;
            case 'productAsc':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('title', 'asc'); // alapértelmezett
        }



        // Pagináció
        $products = $query->paginate($itemsPerPage)->withQueryString();

        $tags = Tag::whereHas('products', function ($q) use ($allProductIds) {
            $q->whereIn('products.id', $allProductIds);
        })->pluck('id', 'name');

        $brands = Brand::whereHas('products', function ($q) use ($allProductIds) {
            $q->whereIn('products.id', $allProductIds);
        })->pluck('id', 'title');


        $attributes = Attribute::select('attributes.id', 'attributes.name', 'product_attributes.value')
            ->join('product_attributes', 'attributes.id', '=', 'product_attributes.attribute_id')
            ->whereIn('product_attributes.product_id', $allProductIds)
            ->where('attributes.show_filter', true)
            ->distinct()
            ->get()
            ->groupBy('name');

        $latest_products = Product::with(['photos'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $allProductsQuery = Product::where('status', 'active');
        $minPrice = $allProductsQuery->min('gross_price');
        $maxPrice = $allProductsQuery->max('gross_price');

        $nav = collect();

        $nav->prepend([
            'title' => 'Termékek',
            'url' => route('products.index'),
        ]);

        $nav->prepend([
            'title' => 'Főoldal',
            'url' => route('index'),
        ]);

        // Ha van szűrés, akkor a product_count a szűrt termékek száma
        if ($tags || $brands || $attributes) {
            $product_count = $products->total();
        } else {
            $product_count = $allProductsQuery->count();
        }

        return view('pages.products.index', [
            'products' => $products,
            'breadcrumbs' => [
                'page_title' => 'Termékek',
                'nav' => $nav
            ],
            'tags' => $tags,
            'brands' => $brands,
            'attributes' => $attributes,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'latest_products' => $latest_products,
            'product_count' => $product_count,
        ]);
    }

    public function resolve($slugs)
    {
        $parts = explode('/', $slugs);
        $last = end($parts);

        // Ellenőrizzük, hogy létezik-e ez slug-ként a termékek között
        $product = Product::where('slug', $last)->first();

        if ($product) {
            return $this->show(implode('/', array_slice($parts, 0, -1)), $last);
        }

        // Különben: csak kategóriaútvonal
        return $this->category($slugs);
    }

    public function category($categorySlugs)
    {
        $slugs = explode('/', $categorySlugs);
        $parent = null;
        foreach ($slugs as $slug) {
            $parent = Category::where('slug', $slug)
                ->when($parent, fn($q) => $q->where('parent_id', $parent->id))
                ->firstOrFail();
        }

        $parent->load('children');
        $categoryIds = $this->collectCategoryIds($parent);

        $request = request();

        // 🔍 Query paraméterek
        $tags = $request->query('tag');
        $brands = $request->query('brand');
        $attributes = $request->query('attribute');
        $sortBy = $request->query('sortBy');
        $itemsPerPage = $request->query('itemsPerPage', 12);

        // 🔎 Alap lekérdezés
        $query = Product::with('category')
            ->whereIn('cat_id', $categoryIds)
            ->where('status', 'active');

        $allProductIds = $query->pluck('id');

        // 🔍 Tag szűrés
        if ($tags) {
            $tagArray = explode('|', $tags);
            $query->whereHas('tags', function ($q) use ($tagArray) {
                $q->whereIn('tag_id', $tagArray);
            });
        }

        // 🔍 Brand szűrés
        if ($brands) {
            $brandArray = explode('|', $brands);
            $query->whereHas('brands', function ($q) use ($brandArray) {
                $q->whereIn('brand_id', $brandArray);
            });
        }

        // 🔍 Attribútum szűrés, ha van
        if ($attributes) {
            $attributeArray = explode('|', $attributes);

            $query->whereHas('attributes', function ($q) use ($attributeArray) {
                foreach ($attributeArray as $attr) {
                    // "id:value" formátum
                    [$attrId, $value] = explode(':', $attr);
                    $q->where(function ($sub) use ($attrId, $value) {
                        $sub->where('attribute_id', $attrId)
                            ->where('value', $value);
                    });
                }
            });
        }

        // 🔃 Rendezés
        switch ($sortBy) {
            case 'productDesc':
                $query->orderBy('title', 'desc');
                break;
            case 'priceAsc':
                $query->orderBy('gross_price', 'asc');
                break;
            case 'priceDesc':
                $query->orderBy('gross_price', 'desc');
                break;
            case 'productAsc':
            default:
                $query->orderBy('title', 'asc');
                break;
        }



        $products = $query->paginate($itemsPerPage)->withQueryString();

        $tags = Tag::whereHas('products', function ($q) use ($allProductIds) {
            $q->whereIn('products.id', $allProductIds);
        })->pluck('id', 'name');

        $brands = Brand::whereHas('products', function ($q) use ($allProductIds) {
            $q->whereIn('products.id', $allProductIds);
        })->pluck('id', 'title');


        $attributes = Attribute::select('attributes.id', 'attributes.name', 'product_attributes.value')
            ->join('product_attributes', 'attributes.id', '=', 'product_attributes.attribute_id')
            ->whereIn('product_attributes.product_id', $allProductIds)
            ->where('attributes.show_filter', true)
            ->distinct()
            ->get()
            ->groupBy('name');

        $latest_products = Product::whereIn('cat_id', $categoryIds)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $allProductsQuery = Product::whereIn('cat_id', $categoryIds)->where('status', 'active');
        $minPrice = $allProductsQuery->min('gross_price');
        $maxPrice = $allProductsQuery->max('gross_price');

        // 🔻 Breadcrumbs
        $nav = collect();
        $current = $parent;
        while ($current) {
            $nav->prepend([
                'title' => $current->title,
                'url' => route('products.resolve', ['slugs' => $current->getFullSlug()])
            ]);
            $current = $current->parent;
        }
        $nav->prepend([
            'title' => 'Termékek',
            'url' => route('products.index'),
        ]);
        $nav->prepend([
            'title' => 'Főoldal',
            'url' => route('index'),
        ]);

        // Ha van szűrés, akkor a product_count a szűrt termékek száma
        if ($tags || $brands || $attributes) {
            $product_count = $products->total();
        } else {
            $product_count = $allProductsQuery->count();
        }

        return view('pages.products.index', [
            'products' => $products,
            'category' => $parent,
            'tags' => $tags,
            'brands' => $brands,
            'attributes' => $attributes,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'latest_products' => $latest_products,
            'breadcrumbs' => [
                'page_title' => $parent->title,
                'nav' => $nav
            ],
            'product_count' => $product_count,
        ]);
    }


    public function show(string $categorySlugs, string $productSlug)
    {
        $product = Product::with(['category', 'photos', 'attributes', 'tags', 'brands'])
            ->where('slug', $productSlug)
            ->firstOrFail();

        WatchedProduct::updateOrCreate(
            [
                'product_id' => $product->id,
                'ip_address' => request()->ip(),
            ],
            [
                'updated_at' => now()
            ]
        );

        $relatedProducts = Product::where('cat_id', $product->cat_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('pages.products.show', compact('product', 'relatedProducts'));
    }

    protected function collectCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->collectCategoryIds($child));
        }

        return $ids;
    }

}
