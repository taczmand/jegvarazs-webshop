<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorksheetProduct extends Model
{
    protected $guarded = [];


    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class, 'worksheet_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
