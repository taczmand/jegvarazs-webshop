<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class OfferProduct extends Model
{
    use LogsActivity;
    protected $guarded = [];


    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
