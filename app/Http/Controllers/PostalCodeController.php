<?php

namespace App\Http\Controllers;

use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    public function searchPostalCodes(Request $request)
    {
        $zip = $request->input('zip');

        // Ha 1-essel kezdődik az irányítószám
        if (preg_match('/^1\d{0,3}$/', $zip)) {
            return response()->json([
                [
                    'zip' => $zip,
                    'city' => 'Budapest',
                ]
            ]);
        }

        $results = PostalCode::where('zip', 'LIKE', "{$zip}%")
            ->limit(10)
            ->get();

        return response()->json($results);
    }
}
