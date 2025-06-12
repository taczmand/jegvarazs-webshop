<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PartnerProduct extends Model
{
    use LogsActivity;

    protected $table = 'products_to_partners';
    protected $guarded = [];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function partner()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
