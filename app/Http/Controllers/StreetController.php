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

        $endpoints = [
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
        ];

        try {
            $response = null;
            $usedEndpoint = null;

            foreach ($endpoints as $endpoint) {
                $candidate = Http::timeout(25)
                    ->withHeaders([
                        'Accept' => 'application/json,text/plain;q=0.9,*/*;q=0.8',
                        'User-Agent' => 'jegvarazs-webshop/1.0',
                    ])
                    ->asForm()
                    ->post($endpoint, [
                        'data' => $query,
                    ]);

                Log::info('Overpass streets search response', [
                    'city' => $city,
                    'q' => $q,
                    'endpoint' => $endpoint,
                    'status' => $candidate->status(),
                    'ok' => $candidate->ok(),
                    'body' => mb_substr((string) $candidate->body(), 0, 2000),
                ]);

                if ($candidate->ok()) {
                    $response = $candidate;
                    $usedEndpoint = $endpoint;
                    break;
                }
            }

            if (!$response) {
                Log::warning('Overpass streets search failed on all endpoints', [
                    'city' => $city,
                    'q' => $q,
                    'endpoints' => $endpoints,
                ]);

                return response()->json([]);
            }
        } catch (\Throwable $e) {
            Log::error('Overpass streets search exception', [
                'city' => $city,
                'q' => $q,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([]);
        }

        $json = $response->json();
        $elements = is_array($json) ? ($json['elements'] ?? []) : [];

        if ($elements === []) {
            Log::info('Overpass streets search empty elements', [
                'city' => $city,
                'q' => $q,
                'endpoint' => $usedEndpoint,
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
