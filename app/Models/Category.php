<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = ['title', 'slug', 'description', 'parent_id', 'status'];

    public function products(){
        return $this->hasMany(Product::class,'cat_id','id');
    }
    public function sub_products(){
        return $this->hasMany(Product::class,'child_cat_id','id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getFullSlug(): string
    {
        $slugs = [];
        $category = $this;

        while ($category) {
            $slugs[] = $category->slug;
            $category = $category->parent;
        }

        return implode('/', array_reverse($slugs));
    }

    public function getFullSlugWithImage(): array
    {
        $slugs = [];
        $category = $this;

        // 🔹 Kategória slug-ok összegyűjtése
        while ($category) {
            $slugs[] = $category->slug;
            $category = $category->parent;
        }
        $fullSlug = implode('/', array_reverse($slugs));

        // 🔹 Kategóriák láncolata (aktuális + szülők)
        $categoryIds = [];
        $category = $this;
        while ($category) {
            $categoryIds[] = $category->id;
            $category = $category->parent;
        }

        // 🔹 Első aktív termék képpel a kategóriákban
        $productWithImage = \App\Models\Product::whereIn('cat_id', $categoryIds)
            ->where('status', 'active')        // csak aktív termékek
            ->whereHas('photos')               // csak képpel rendelkező termékek
            ->with('photos')
            ->orderBy('created_at', 'asc')     // rendezés, hogy mindig ugyanaz az első legyen
            ->first();

        $photo = $productWithImage?->photos->first() ?? null;

        return [
            'slug' => $fullSlug,
            'product_with_image' => $photo,
        ];
    }

}
