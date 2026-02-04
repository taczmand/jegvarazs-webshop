<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use LogsActivity;

    protected $guarded = [];

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }
}
