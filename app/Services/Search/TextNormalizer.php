<?php

namespace App\Services\Search;

class TextNormalizer
{
    public function normalize(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $text = mb_strtolower($text);

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if (is_string($converted) && $converted !== '') {
            $text = $converted;
        }

        $text = preg_replace('/[^a-z0-9\s\-_.]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * @return array<int, string>
     */
    public function tokens(string $text): array
    {
        $normalized = $this->normalize($text);
        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $normalized) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn ($t) => $t !== ''));

        return $parts;
    }
}
