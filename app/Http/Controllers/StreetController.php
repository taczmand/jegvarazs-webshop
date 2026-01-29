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

        $results = Cache::remember($cacheKey, now()->addHours(6), function () use ($city, $q) {
            $cityEscaped = addcslashes($city, "\\\"");
            $regexEscaped = preg_quote($q, '/');

            $query = <<<OVERPASS
[out:json][timeout:25];
area["name"="{$cityEscaped}"]["boundary"="administrative"]->.searchArea;
(
  way["highway"]["name"~"^{$regexEscaped}",i](area.searchArea);
);
out tags;
OVERPASS;

            $response = Http::timeout(25)
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $query,
                ]);

            if (!$response->ok()) {
                return [];
            }

            $json = $response->json();
            $elements = is_array($json) ? ($json['elements'] ?? []) : [];

            $names = [];
            foreach ($elements as $el) {
                $name = $el['tags']['name'] ?? null;
                if (!$name) {
                    continue;
                }
                $names[] = $name;
            }

            $names = array_values(array_unique($names));
            sort($names, SORT_NATURAL | SORT_FLAG_CASE);

            return array_slice($names, 0, 20);
        });

        return response()->json($results);
    }
}
