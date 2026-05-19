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

        $out = [];
        $i = 0;
        $count = count($parts);
        while ($i < $count) {
            $t = $parts[$i];

            if (($i + 1) < $count && preg_match('/^\d+(?:[\.,]\d+)?$/u', $t)) {
                $next = $parts[$i + 1];
                if (preg_match('/^[a-z]{1,3}$/u', $next)) {
                    $out[] = $t . $next;
                    $i += 2;
                    continue;
                }
            }

            $out[] = $t;

            // Split concatenated alphanumeric tokens (e.g. delta3 -> delta 3)
            // Keep the original token too.
            $spaced = preg_replace('/(?<=\p{L})(?=\d)|(?<=\d)(?=\p{L})/u', ' ', $t);
            if (is_string($spaced) && $spaced !== $t) {
                $more = preg_split('/\s+/u', $spaced) ?: [];
                $more = array_values(array_filter(array_map('trim', $more), fn ($x) => $x !== ''));

                if (count($more) >= 2) {
                    foreach ($more as $m) {
                        if (preg_match('/^[a-z]{1,3}$/u', $m)) {
                            continue;
                        }
                        $out[] = $m;
                    }
                }
            }

            $i++;
        }

        return array_values(array_unique(array_filter($out, fn ($v) => $v !== '')));
    }
}
