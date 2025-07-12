<?php

namespace App\Helpers;

class AmountToText
{
    /**
     * Convert an integer amount to its Hungarian text representation.
     *
     * @param int $amount The amount to convert.
     * @return string The amount in Hungarian text.
     */
    public static function convert(int $amount): string
    {
        $fmt = new \NumberFormatter('hu_HU', \NumberFormatter::SPELLOUT);
        $text = $fmt->format($amount);

        if ($text === false) {
            return $amount . ' forint';
        }

        $text = mb_strtolower($text);

        // "ezer" után kötőjel beszúrása, ha utána még van karakter (pl. kétszázhatvan)
        if (str_contains($text, 'ezer')) {
            $text = preg_replace('/ezer(?!$)/u', 'ezer-', $text);
        }

        // "-nulla" eltávolítása, ha előfordul
        $text = str_replace('-nulla', '', $text);

        return $text;
    }
}
