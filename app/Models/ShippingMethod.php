<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'code', 'price', 'status'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
