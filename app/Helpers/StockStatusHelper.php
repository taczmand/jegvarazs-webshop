<?php

namespace App\Helpers;

class StockStatusHelper
{
    public static function resolve(int $stock): array|null
    {
        foreach (config('stock_statuses') as $status) {
            if (($status['match'])($stock)) {
                return $status;
            }
        }

        return null;
    }

    public static function label(int $stock): string
    {
        $status = self::resolve($stock);
        return $status['label'] ?? 'Ismeretlen';
    }

    public static function color(int $stock): string
    {
        $status = self::resolve($stock);
        return $status['color'] ?? 'secondary';
    }

    public static function slug(int $stock): string
    {
        $status = self::resolve($stock);
        return $status['slug'] ?? 'unknown';
    }
}
