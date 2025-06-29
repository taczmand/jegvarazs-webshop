<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worksheet extends Model
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function photos()
    {
        return $this->hasMany(WorksheetImage::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'worksheet_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

}
