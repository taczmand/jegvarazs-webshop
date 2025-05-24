<?php

namespace App\Services\Admin;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Lekéri egy termék összes szükséges adatát részletesen,
     * beleértve a fotókat, attribútumokat és tag-eket meg bármit, ami még lesz...
     */
    public function getProductWithPivotMeta($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'product' => $product->getAttributes(),
            'product_photos' => $product->photos()->get(),
            'assigned_tags' => $product->tags()->select('tags.id')->pluck('tags.id'), // csak hozzárendelt tag ID-k
            'assigned_attributes' => $product->attributes()->get(),  // hozzárendelt attribútumok pivot adattal (value),
        ]);
    }

    /**
     * Termékhez rendelhető metaadatok lekérése.
     *
     */
    public function getAllMeta() {
        $tags = Tag::all();
        $attributes = Attribute::all();
        $brands = Brand::active()->get();
        $categories = Category::with(['children' => function ($query) {
            $query->where('status', 'active');
        }])->where('status', 'active')->get();

        return response()->json([
            'tags' => $tags,
            'attributes' => $attributes,
            'brands' => $brands,
            'categories' => $categories
        ]);
    }

    /**
     * Új termék létrehozása
     *
     * @param array $data
     * @return array ['success' => bool, 'data' => Product|null, 'error' => string|null]
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Termék alapadatok mentése
            $product = Product::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'] ?? null,
                'stock' => $data['stock'],
                'price' => $data['price'],
                'discount' => 0,
                'cat_id' => $data['category_id'] ?? null,
                'child_cat_id' => $data['subcategory_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null
            ]);

            // Attribútumok mentése (kulcs szerint)
            $attributes = [];

            foreach ($data['attributes'] ?? [] as $attributeId => $value) {
                if ($value !== null) {
                    $attributes[$attributeId] = ['value' => $value];
                }
            }

            $product->attributes()->sync($attributes);

            // Címkék mentése
            if (!empty($data['tags'])) {
                $product->tags()->sync($data['tags']);
            }

            // Új képek mentése
            if (!empty($data['new_photos'])) {
                $photos = [];

                foreach ($data['new_photos'] as $photo) {
                    $path = $photo->store('products', 'public');
                    $photos[] = ['path' => $path];
                }

                $product->photos()->createMany($photos);
            }
            return $product;
        });
    }

    public function update(array $data)
    {
        $product = Product::findOrFail($data['id']);

        return DB::transaction(function () use ($data, $product) {

            // Termék alapadatok frissítése
            $product->update([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'] ?? null,
                'stock' => $data['stock'],
                'price' => $data['price'],
                'cat_id' => $data['category_id'] ?? null,
                'child_cat_id' => $data['subcategory_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null
            ]);

            // Attribútumok frissítése (kulcs szerint)
            $attributes = [];

            foreach ($data['attributes'] ?? [] as $attributeId => $value) {
                if ($value !== null) {
                    $attributes[$attributeId] = ['value' => $value];
                }
            }

            $product->attributes()->sync($attributes);

            // Címkék frissítése
            if (!empty($data['tags'])) {
                $product->tags()->sync($data['tags']);
            } else {
                $product->tags()->detach();
            }

            // Új képek mentése
            if (!empty($data['new_photos'])) {
                $photos = [];

                foreach ($data['new_photos'] as $photo) {
                    $path = $photo->store('products', 'public');
                    $photos[] = ['path' => $path];
                }

                $product->photos()->createMany($photos);
            }

            return $product;
        });
    }
}
