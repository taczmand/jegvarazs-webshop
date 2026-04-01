<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class BasicData extends Model
{
    use LogsActivity;
    protected $fillable = ['key', 'value'];

    public static function getValueByKey(string $key, $default = null)
    {
        $value = static::query()->where('key', $key)->value('value');
        return $value !== null ? $value : $default;
    }

    public static function getIntByKey(string $key, int $default, ?int $min = null, ?int $max = null): int
    {
        $raw = static::getValueByKey($key, null);
        $value = is_numeric($raw) ? (int) $raw : $default;

        if ($min !== null && $value < $min) {
            $value = $min;
        }
        if ($max !== null && $value > $max) {
            $value = $max;
        }

        return $value;
    }

    public static function getVehicleKmRequiredDay(): int
    {
        return static::getIntByKey('vehicle_km_required_day', 1, 1, 28);
    }

}
