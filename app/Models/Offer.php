<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_products')
            ->withPivot('gross_price')
            ->withTimestamps();
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
