<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $cityEscaped = addcslashes($city, "\\\"");
        $regexEscaped = preg_quote($q, '/');

        $query = <<<OVERPASS
[out:json][timeout:25];
{{geocodeArea:"{$cityEscaped}"}}->.searchArea;
(
  way["highway"]["name"~"^{$regexEscaped}",i](area.searchArea);
);
out tags;
OVERPASS;

        try {
            $response = Http::timeout(25)
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $query,
                ]);

            Log::info('Overpass streets search response', [
                'city' => $city,
                'q' => $q,
                'status' => $response->status(),
                'ok' => $response->ok(),
                'body' => mb_substr((string) $response->body(), 0, 2000),
            ]);
        } catch (\Throwable $e) {
            Log::error('Overpass streets search exception', [
                'city' => $city,
                'q' => $q,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([]);
        }

        if (!$response->ok()) {
            return response()->json([]);
        }

        $json = $response->json();
        $elements = is_array($json) ? ($json['elements'] ?? []) : [];

        if ($elements === []) {
            Log::info('Overpass streets search empty elements', [
                'city' => $city,
                'q' => $q,
            ]);
        }

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

        return response()->json(array_slice($names, 0, 20));
    }
}
