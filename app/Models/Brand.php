<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
