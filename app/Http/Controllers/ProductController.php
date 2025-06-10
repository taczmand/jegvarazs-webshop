<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(12);
        return view('pages.products.index', compact('products'));
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

        $products = Product::whereIn('cat_id', $categoryIds)->paginate(12);

        $tags = Tag::all()->pluck('id', 'name')->toArray();

        $latest_products = Product::whereIn('cat_id', $categoryIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();


        // Lekérdezzük a teljes terméshalmazt ár elemzéshez
        $allProductsQuery = Product::whereIn('cat_id', $categoryIds);

        $minPrice = $allProductsQuery->min('gross_price');
        $maxPrice = $allProductsQuery->max('gross_price');

        return view('pages.products.index', [
            'products' => $products,
            'category' => $parent,
            'tags' => $tags,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'latest_products' => $latest_products,
        ]);
    }
    public function show(string $categorySlugs, string $productSlug)
    {
        $product = Product::with(['category', 'photos'])
            ->where('slug', $productSlug)
            ->firstOrFail();

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
