<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Worksheet extends Model
{
    use LogsActivity;

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'worksheet_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }
    public function workers()
    {
        return $this->belongsToMany(User::class, 'worksheet_workers', 'worksheet_id', 'worker_id')
            ->withTimestamps();
    }

}
