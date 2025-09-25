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

        // 🔹 Képet keresünk rekurzívan a kategóriában és a szülőkben
        $photo = $this->findFirstProductImage();

        return [
            'slug' => $fullSlug,
            'product_with_image' => $photo,
        ];
    }

    protected function findFirstProductImage()
    {
        // 🔹 Aktív termék képpel a jelenlegi kategóriában
        $productWithImage = $this->products()
            ->where('status', 'active')
            ->whereHas('photos')
            ->with('photos')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($productWithImage) {
            return $productWithImage->photos->first();
        }

        // 🔹 Ha nincs, nézzük a szülőt
        if ($this->parent) {
            return $this->parent->findFirstProductImage();
        }

        return null; // sehol nincs kép
    }


}
