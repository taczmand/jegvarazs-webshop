<?php

namespace App\Services\Admin;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\CartItem;
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

        $cartOwners = CartItem::query()
            ->where('product_id', $product->id)
            ->with(['cart.user'])
            ->get()
            ->map(function (CartItem $item) {
                return $item->cart?->user;
            })
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($customer) {
                $name = $customer->getAttribute('name');
                if (!$name) {
                    $first = (string) ($customer->getAttribute('first_name') ?? '');
                    $last = (string) ($customer->getAttribute('last_name') ?? '');
                    $name = trim($first . ' ' . $last);
                }

                return [
                    'id' => $customer->getAttribute('id'),
                    'name' => $name ?: null,
                    'email' => $customer->getAttribute('email'),
                    'phone' => $customer->getAttribute('phone'),
                ];
            });

        return response()->json([
            'product' => $product->getAttributes(),
            'product_photos' => $product->photos()->get(),
            'assigned_tags' => $product->tags()->select('tags.id')->pluck('tags.id'), // csak hozzárendelt tag ID-k
            'assigned_attributes' => $product->attributes()->get(),  // hozzárendelt attribútumok pivot adattal (value),
            'cart_owners' => $cartOwners,
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

            // Attribútumok mentése
            $attributes = [];

            foreach ($data['attributes'] ?? [] as $attributeId => $value) {
                if ($value !== null && !str_contains($value, '|')) {
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

                foreach ($data['new_photos'] as $index => $photo) {

                    $extension = strtolower($photo->getClientOriginalExtension());

                    $path = $photo->store('products', 'public');
                    $fullPath = Storage::disk('public')->path($path);

                    // Csak JPG/PNG → WEBP
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        try {
                            $imagick = new \Imagick($fullPath);

                            // WEBP beállítások
                            $imagick->setImageFormat('webp');
                            $imagick->setImageCompressionQuality(75);
                            $imagick->stripImage();

                            // Vízjel betöltése
                            $watermark = new \Imagick(env('STATIC_MEDIA_PATH') . '/uj_logo_szeles_transzparens.png');

                            // Halványítás
                            $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, 0.1, \Imagick::CHANNEL_ALPHA);

                            // Méretezés
                            $watermark->thumbnailImage($imagick->getImageWidth() / 1.5, 0);

                            // Pozicionálás
                            $x = ($imagick->getImageWidth() - $watermark->getImageWidth()) / 2;
                            $y = ($imagick->getImageHeight() - $watermark->getImageHeight()) / 2;

                            // Alpha csatorna
                            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                            // Rákompozitálás
                            $imagick->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);

                            // Mentés új webp fájlba
                            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $fullPath);
                            $imagick->writeImage($webpPath);
                            $imagick->destroy();

                            // Eredeti törlése
                            unlink($fullPath);

                            // Új relatív path
                            $path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);

                        } catch (\Exception $e) {
                            \Log::error("WEBP konverzió sikertelen: {$fullPath} - {$e->getMessage()}");
                        }
                    }

                    // Fotók mentése a DB-be
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
                if ($value !== null && !str_contains($value, '|')) {
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

                    // Csak képeknél optimalizálunk + webp konverzió
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        try {
                            $imagick = new \Imagick($fullPath);

                            // WEBP beállítások
                            $imagick->setImageFormat('webp');
                            $imagick->setImageCompressionQuality(75);
                            $imagick->stripImage();

                            // Vízjel betöltése
                            $watermark = new \Imagick(env('STATIC_MEDIA_PATH') . '/uj_logo_szeles_transzparens.png');

                            // Finom átlátszóság
                            $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, 0.1, \Imagick::CHANNEL_ALPHA);

                            // Méretezés a fő képhez
                            $watermark->thumbnailImage($imagick->getImageWidth() / 1.5, 0);

                            // Középre pozicionálás
                            $x = ($imagick->getImageWidth() - $watermark->getImageWidth()) / 2;
                            $y = ($imagick->getImageHeight() - $watermark->getImageHeight()) / 2;

                            // Biztos alpha csatorna
                            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                            // Vízjel rákompozitálása
                            $imagick->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);

                            // Új fájlnév: .webp
                            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $fullPath);

                            // Mentés webp-be
                            $imagick->writeImage($webpPath);
                            $imagick->destroy();

                            // Eredeti fájl törlése
                            unlink($fullPath);

                            // A path frissítése, hogy adatbázisba a webp menjen
                            $path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);

                        } catch (\Exception $e) {
                            \Log::error("WEBP konverzió sikertelen: {$fullPath} - {$e->getMessage()}");
                        }
                    }

                    $photos[] = ['path' => $path];
                }

                $product->photos()->createMany($photos);
            }

            return $product;
        });
    }

    public function uploadProductPhotos($data)
    {
        $product = Product::findOrFail($data['id']);

        if (!empty($data['new_photos'])) {
            $photos = [];

            foreach ($data['new_photos'] as $photo) {

                // Eredeti fájl mentése (még nem WebP)
                $originalPath = $photo->store('products', 'public');
                $fullOriginalPath = Storage::disk('public')->path($originalPath);

                // Új WebP fájlnév
                $filenameWithoutExt = pathinfo($originalPath, PATHINFO_FILENAME);
                $webpFilename = $filenameWithoutExt . '.webp';
                $webpPath = 'products/' . $webpFilename;
                $fullWebpPath = Storage::disk('public')->path($webpPath);

                try {
                    $imagick = new \Imagick($fullOriginalPath);

                    // Mindig WebP-re konvertálunk
                    $imagick->setImageFormat('webp');

                    // WebP tömörítés minősége
                    $imagick->setImageCompressionQuality(75);

                    // Metaadatok törlése
                    $imagick->stripImage();

                    /**
                     * VÍZJEL
                     */
                    $watermark = new \Imagick(env('STATIC_MEDIA_PATH') . '/uj_logo_szeles_transzparens.png');
                    $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, 0.1, \Imagick::CHANNEL_ALPHA);

                    $watermark->thumbnailImage($imagick->getImageWidth() / 1.5, 0);

                    $x = ($imagick->getImageWidth() - $watermark->getImageWidth()) / 2;
                    $y = ($imagick->getImageHeight() - $watermark->getImageHeight()) / 2;

                    $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
                    $imagick->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);

                    // WebP mentés
                    $imagick->writeImage($fullWebpPath);

                    $imagick->clear();
                    $imagick->destroy();

                    // Eredeti JPG/PNG törlése
                    Storage::disk('public')->delete($originalPath);

                } catch (\Exception $e) {
                    \Log::error("WEBP konverzió vagy vízjelezés sikertelen: {$fullOriginalPath} - {$e->getMessage()}");
                    continue;
                }

                // Csak a WebP fájlt tároljuk adatbázisban
                $photos[] = ['path' => $webpPath];
            }

            $product->photos()->createMany($photos);
        }
    }

}
