<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'contract_products')
            ->withPivot('gross_price', 'product_qty')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function worksheets()
    {
        return $this->hasMany(Worksheet::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

}
