<?php

namespace App\Http\Controllers;

use App\Models\SensorEvent;
use Illuminate\Http\Request;

class SensorEventController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:64'],
            'event' => ['nullable', 'string', 'max:50'],
            'sensor' => ['nullable', 'string', 'max:50'],
            'value' => ['nullable'],
            'occurred_at' => ['nullable', 'date'],
            'ip_address' => ['nullable', 'string', 'max:45']
        ]);

        $data['occurred_at'] = $data['occurred_at'] ?? now();

        $event = SensorEvent::create($data);

        return response()->json([
            'ok' => true,
            'id' => $event->id,
        ], 201);
    }
}
