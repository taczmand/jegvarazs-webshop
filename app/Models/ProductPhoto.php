<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ProductPhoto extends Model
{
    use LogsActivity;

    protected $fillable = ['path'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
