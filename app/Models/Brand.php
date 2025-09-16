<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use LogsActivity;

    protected $fillable = ['title', 'slug', 'logo', 'status'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
