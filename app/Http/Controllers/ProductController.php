<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\WatchedProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(12);

        $tags = Tag::all()->pluck('id', 'name')->toArray();

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
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'latest_products' => $latest_products,
            'product_count' => $allProductsQuery->count(),
        ]);
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

        $products = Product::whereIn('cat_id', $categoryIds)->where('status', 'active')->paginate(12);

        $tags = Tag::all()->pluck('id', 'name')->toArray();

        $latest_products = Product::whereIn('cat_id', $categoryIds)->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $allProductsQuery = Product::whereIn('cat_id', $categoryIds)->where('status', 'active');
        $minPrice = $allProductsQuery->min('gross_price');
        $maxPrice = $allProductsQuery->max('gross_price');

        // 🔻 Breadcrumbs felépítése
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
        $product = Product::with(['category', 'photos'])
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

        return view('pages.products.show', compact('product'));
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

    protected function collectCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->collectCategoryIds($child));
        }

        return $ids;
    }

}
