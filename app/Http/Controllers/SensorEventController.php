<?php

namespace App\Http\Controllers;

use App\Models\SensorEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorEventController extends Controller
{
    public function store(Request $request)
    {
        $events = $request->input('events');

        if (is_array($events)) {
            $validated = $request->validate([
                'events' => ['required', 'array', 'min:1', 'max:200'],
                'events.*.device_id' => ['required', 'string', 'max:64'],
                'events.*.event' => ['nullable', 'string', 'max:50'],
                'events.*.sensor' => ['nullable', 'string', 'max:50'],
                'events.*.value' => ['nullable'],
                'events.*.event_time' => ['nullable', 'date'],
                'events.*.occurred_at' => ['nullable', 'date'],
                'events.*.ip_address' => ['nullable', 'string', 'max:45'],
            ]);

            $rows = [];
            $now = now();
            foreach (($validated['events'] ?? []) as $e) {
                $occurredAt = $e['event_time'] ?? $e['occurred_at'] ?? $now;
                unset($e['event_time']);
                $e['occurred_at'] = $occurredAt;
                $e['created_at'] = $now;
                $e['updated_at'] = $now;
                $rows[] = $e;
            }

            DB::transaction(function () use ($rows) {
                SensorEvent::query()->insert($rows);
            });

            return response()->json([
                'ok' => true,
                'count' => count($rows),
            ], 201);
        }

        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:64'],
            'event' => ['nullable', 'string', 'max:50'],
            'sensor' => ['nullable', 'string', 'max:50'],
            'value' => ['nullable'],
            'event_time' => ['nullable', 'date'],
            'occurred_at' => ['nullable', 'date'],
            'ip_address' => ['nullable', 'string', 'max:45'],
        ]);

        $data['occurred_at'] = $data['event_time'] ?? $data['occurred_at'] ?? now();
        unset($data['event_time']);

        $event = SensorEvent::create($data);

        return response()->json([
            'ok' => true,
            'id' => $event->id,
        ], 201);
    }
}
