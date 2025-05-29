<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use LogsActivity;

    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'attribute_product')
            ->withPivot('value')
            ->withTimestamps();
    }
}
