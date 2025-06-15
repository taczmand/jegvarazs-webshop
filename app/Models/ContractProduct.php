<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractProduct extends Model
{
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
