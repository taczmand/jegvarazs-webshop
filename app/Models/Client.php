<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected function email(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (is_null($value)) {
                    return null;
                }

                if (!is_string($value)) {
                    return $value;
                }

                $value = trim($value);

                return $value === '' ? null : mb_strtolower($value);
            }
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }
}
