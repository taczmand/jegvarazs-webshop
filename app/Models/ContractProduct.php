<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ContractProduct extends Model
{
    use LogsActivity;
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
