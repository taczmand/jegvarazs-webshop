<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAddress extends Model
{
    use LogsActivity;

    protected $fillable = [
        'client_id',
        'label',
        'country',
        'zip_code',
        'city',
        'address_line',
        'is_default',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
