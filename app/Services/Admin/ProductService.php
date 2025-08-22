<?php

namespace App\Services\Admin;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\TaxCategory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $taxes = TaxCategory::all();
        $categories = Category::with(['children' => function ($query) {
            $query->where('status', 'active');
        }])->where('status', 'active')->get();

        return response()->json([
            'tags' => $tags,
            'attributes' => $attributes,
            'brands' => $brands,
            'categories' => $categories,
            'taxes' => $taxes
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
                'stock' => $data['stock'] ?? 0,
                'unit_qty' => $data['unit_qty'] ?? 1,
                'gross_price' => $data['gross_price'] ?? 0,
                'partner_gross_price' => $data['partner_gross_price'] ?? $data['gross_price'] ?? 0,
                'is_offerable' => $data['is_offerable'] ?? false,
                'is_selectable_by_installer' => $data['is_selectable_by_installer'] ?? false,
                'tax_id' => $data['tax_id'],
                'cat_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'status' => $data['status'] ?? 'inactive',
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

            // Új képek mentése, első kép lesz az is_main = 1
            if (!empty($data['new_photos'])) {
                $photos = [];

                foreach ($data['new_photos'] as $index => $photo) {

                    $extension = strtolower($photo->getClientOriginalExtension());

                    $path = $photo->store('products', 'public');

                    $fullPath = Storage::disk('public')->path($path);

                    // Csak képeknél optimalizálunk
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        try {
                            $imagick = new \Imagick($fullPath);

                            if (in_array($extension, ['jpg', 'jpeg'])) {
                                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                                $imagick->setImageCompressionQuality(75);
                            } elseif ($extension === 'png') {
                                $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                                $imagick->setImageCompressionQuality(75);
                            }

                            $imagick->stripImage();

                            // Vízjel betöltése
                            $watermark = new \Imagick(public_path('static_media/uj_logo_szeles_transzparens.png'));

                            // Csak az alpha csatorna átlátszóságát csökkentjük
                            $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, 0.2, \Imagick::CHANNEL_ALPHA);

                            // Méretezés a fő képhez
                            $watermark->thumbnailImage($imagick->getImageWidth() / 1.5, 0);

                            // Középre helyezés
                            $x = ($imagick->getImageWidth() - $watermark->getImageWidth()) / 2;
                            $y = ($imagick->getImageHeight() - $watermark->getImageHeight()) / 2;

                            // Biztos alpha csatorna a fő képen
                            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                            // Kompozitálás
                            $imagick->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);

                            // Mentés
                            $imagick->writeImage($fullPath);
                            $imagick->destroy();
                        } catch (\Exception $e) {
                            \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                        }
                    }

                    $photos[] = [
                        'path' => $path,
                        'is_main' => $index === 0 ? 1 : 0
                    ];
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
                'stock' => $data['stock'] ?? 0,
                'unit_qty' => $data['unit_qty'] ?? 1,
                'gross_price' => $data['gross_price'] ?? 0,
                'partner_gross_price' => $data['partner_gross_price'] ?? $data['gross_price'] ?? 0,
                'is_offerable' => $data['is_offerable'] ?? false,
                'is_selectable_by_installer' => $data['is_selectable_by_installer'] ?? false,
                'tax_id' => $data['tax_id'],
                'cat_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'status' => $data['status'],
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

                    $extension = strtolower($photo->getClientOriginalExtension());

                    $path = $photo->store('products', 'public');

                    $fullPath = Storage::disk('public')->path($path);

                    // Csak képeknél optimalizálunk
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        try {
                            $imagick = new \Imagick($fullPath);

                            if (in_array($extension, ['jpg', 'jpeg'])) {
                                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                                $imagick->setImageCompressionQuality(75);
                            } elseif ($extension === 'png') {
                                $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                                $imagick->setImageCompressionQuality(75);
                            }

                            $imagick->stripImage();

                            // Vízjel betöltése
                            $watermark = new \Imagick(public_path('static_media/uj_logo_szeles_transzparens.png'));

                            // Csak az alpha csatorna átlátszóságát csökkentjük
                            $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, 0.2, \Imagick::CHANNEL_ALPHA);

                            // Méretezés a fő képhez
                            $watermark->thumbnailImage($imagick->getImageWidth() / 1.5, 0);

                            // Középre helyezés
                            $x = ($imagick->getImageWidth() - $watermark->getImageWidth()) / 2;
                            $y = ($imagick->getImageHeight() - $watermark->getImageHeight()) / 2;

                            // Biztos alpha csatorna a fő képen
                            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                            // Kompozitálás
                            $imagick->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);

                            $imagick->writeImage($fullPath);
                            $imagick->destroy();
                        } catch (\Exception $e) {
                            \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                        }
                    }

                    $photos[] = ['path' => $path];
                }

                $product->photos()->createMany($photos);
            }

            return $product;
        });
    }
}
