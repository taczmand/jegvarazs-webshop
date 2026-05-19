<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StreetController extends Controller
{
    public function search(Request $request)
    {
        $city = trim((string) $request->query('city', ''));
        $q = trim((string) $request->query('q', ''));

        if ($city === '' || $q === '') {
            return response()->json([]);
        }

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'overpass_streets:' . md5(mb_strtolower($city) . '|' . mb_strtolower($q));

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return response()->json($cached);
        }

        $cityEscaped = addcslashes($city, "\\\"");
        $regexEscaped = preg_quote($q, '/');

        $buildOverpassQuery = function (bool $startsWith) use ($cityEscaped, $regexEscaped): string {
            $pattern = $startsWith ? "^{$regexEscaped}" : $regexEscaped;

            return <<<OVERPASS
[out:json][timeout:25];
{{geocodeArea:"{$cityEscaped}"}}->.searchArea;
(
  way["highway"]["name"~"{$pattern}",i](area.searchArea);
);
out tags;
OVERPASS;
        };

        $endpoints = [
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
        ];

        $names = [];
        foreach ([true, false] as $startsWith) {
            $queryBody = $buildOverpassQuery($startsWith);

            foreach ($endpoints as $url) {
                try {
                    $response = Http::timeout(25)
                        ->retry(2, 300)
                        ->asForm()
                        ->post($url, ['data' => $queryBody]);

                    if (!$response->ok()) {
                        continue;
                    }

                    $json = $response->json();
                    $elements = is_array($json) ? ($json['elements'] ?? []) : [];

                    foreach ($elements as $el) {
                        $name = $el['tags']['name'] ?? null;
                        if (!$name) {
                            continue;
                        }
                        $names[] = $name;
                    }

                    if ($names !== []) {
                        break 2;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        $names = array_values(array_unique($names));
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);
        $results = array_slice($names, 0, 20);

        // Üres eredményt ne cache-eljünk hosszan (Overpass rate-limit / átmeneti hiba gyakori)
        Cache::put($cacheKey, $results, $results === [] ? now()->addMinutes(5) : now()->addHours(6));

        return response()->json($results);
    }
}
