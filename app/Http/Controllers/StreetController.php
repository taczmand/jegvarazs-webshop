<?php

namespace App\Http\Controllers;

use App\Models\Street;
use Illuminate\Http\Request;

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

        $result = Street::query()
            ->where('city', $city)
            ->where('street_name', 'like', $q . '%')
            ->orderBy('street_name')
            ->limit(20)
            ->pluck('street_name')
            ->values()
            ->all();

        return response()->json($result);
    }
}
