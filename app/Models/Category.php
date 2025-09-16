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

        while ($category) {
            $slugs[] = $category->slug;
            $category = $category->parent;
        }

        $fullSlug = implode('/', array_reverse($slugs));

        // Az első termék, ami képpel rendelkezik
        $productWithImage = $this->products()
            ->whereHas('photos')
            ->with('photos')
            ->first();

        if ($productWithImage) {
            // Ha van ilyen termék, akkor visszaadjuk az első képét
            $photo = $productWithImage->photos->first();
        } else {
            $photo = null;
        }

        return [
            'slug' => $fullSlug,
            'product_with_image' => $photo,
        ];
    }
}
