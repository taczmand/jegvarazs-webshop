<?php

namespace App\Http\Controllers;

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
        $sortBy = $request->query('sortBy');

        // Alap query
        $query = Product::with('category')
            ->where('status', 'active');

        // 🔍 Tag szűrés, ha van
        if ($tags) {
            $tagArray = explode(',', $tags);
            $query->whereHas('tags', function ($q) use ($tagArray) {
                $q->whereIn('tag_id', $tagArray);
            });
        }

        // 🔍 Brand szűrés, ha van
        if ($brands) {
            $brandArray = explode(',', $brands);
            $query->whereHas('brands', function ($q) use ($brandArray) {
                $q->whereIn('brand_id', $brandArray);
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
        $products = $query->paginate(12)->withQueryString();

        $tags = Tag::all()->pluck('id', 'name')->toArray();
        $brands = Brand::where('status', 'active')->pluck('id', 'title')->toArray();

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

        return view('pages.products.index', [
            'products' => $products,
            'breadcrumbs' => [
                'page_title' => 'Termékek',
                'nav' => $nav,
                'cover_image' => null,
            ],
            'tags' => $tags,
            'brands' => $brands,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'latest_products' => $latest_products,
            'product_count' => $allProductsQuery->count(),
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
        $sortBy = $request->query('sortBy');

        // 🔎 Alap lekérdezés
        $query = Product::with('category')
            ->whereIn('cat_id', $categoryIds)
            ->where('status', 'active');

        // 🔍 Tag szűrés
        if ($tags) {
            $tagArray = explode(',', $tags);
            $query->whereHas('tags', function ($q) use ($tagArray) {
                $q->whereIn('tag_id', $tagArray);
            });
        }

        // 🔍 Brand szűrés
        if ($brands) {
            $brandArray = explode(',', $brands);
            $query->whereHas('brands', function ($q) use ($brandArray) {
                $q->whereIn('brand_id', $brandArray);
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

        $products = $query->paginate(12)->withQueryString();

        $tags = Tag::all()->pluck('id', 'name')->toArray();
        $brands = Brand::where('status', 'active')->pluck('id', 'title')->toArray();

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

        return view('pages.products.index', [
            'products' => $products,
            'category' => $parent,
            'tags' => $tags,
            'brands' => $brands,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'latest_products' => $latest_products,
            'breadcrumbs' => [
                'page_title' => $parent->title,
                'nav' => $nav,
                'cover_image' => $parent->cover_image ?? null,
            ],
            'product_count' => $allProductsQuery->count(),
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
