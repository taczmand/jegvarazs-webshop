<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Regulation extends Model
{
    protected $guarded = [];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
