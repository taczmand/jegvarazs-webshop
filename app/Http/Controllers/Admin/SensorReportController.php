<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SensorEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorReportController extends Controller
{
    public function index()
    {
        abort_unless(auth('admin')->user() && auth('admin')->user()->can('view-sensor-reports'), 403);

        $deviceIds = SensorEvent::query()
            ->select('device_id')
            ->whereNotNull('device_id')
            ->distinct()
            ->orderBy('device_id')
            ->pluck('device_id')
            ->all();

        $year = now()->year;

        return view('admin.statistics.sensors', compact('deviceIds', 'year'));
    }

    public function show(string $deviceId, Request $request)
    {
        abort_unless(auth('admin')->user() && auth('admin')->user()->can('view-sensor-reports'), 403);

        $year = (int) ($request->query('year') ?? now()->year);

        $dateExpr = 'COALESCE(occurred_at, created_at)';

        $minYear = SensorEvent::query()
            ->where('device_id', $deviceId)
            ->selectRaw('MIN(YEAR(' . $dateExpr . ')) as y')
            ->value('y');

        $maxYear = SensorEvent::query()
            ->where('device_id', $deviceId)
            ->selectRaw('MAX(YEAR(' . $dateExpr . ')) as y')
            ->value('y');

        $minYear = $minYear ? (int) $minYear : $year;
        $maxYear = $maxYear ? (int) $maxYear : $year;

        if ($year < $minYear) {
            $year = $minYear;
        }

        if ($year > $maxYear) {
            $year = $maxYear;
        }

        $from = Carbon::create($year, 1, 1, 0, 0, 0);
        $to = Carbon::create($year, 12, 31, 23, 59, 59);

        $rows = SensorEvent::query()
            ->where('device_id', $deviceId)
            ->whereBetween(DB::raw($dateExpr), [$from, $to])
            ->selectRaw('MONTH(' . $dateExpr . ') as m')
            ->selectRaw('DAY(' . $dateExpr . ') as d')
            ->selectRaw('COUNT(*) as c')
            ->groupBy('m', 'd')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row->m][(int) $row->d] = (int) $row->c;
        }

        return view('admin.statistics.sensors_calendar', [
            'deviceId' => $deviceId,
            'year' => $year,
            'minYear' => $minYear,
            'maxYear' => $maxYear,
            'counts' => $counts,
        ]);
    }

    public function day(string $deviceId, Request $request)
    {
        abort_unless(auth('admin')->user() && auth('admin')->user()->can('view-sensor-reports'), 403);

        $day = (string) $request->query('day', '');
        try {
            $date = Carbon::createFromFormat('Y-m-d', $day);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid day format. Expected Y-m-d.',
            ], 422);
        }

        $dateExpr = 'COALESCE(occurred_at, created_at)';
        $from = $date->copy()->startOfDay();
        $to = $date->copy()->endOfDay();

        $events = SensorEvent::query()
            ->where('device_id', $deviceId)
            ->whereBetween(DB::raw($dateExpr), [$from, $to])
            ->orderBy(DB::raw($dateExpr))
            ->get([
                'id',
                'occurred_at',
                'created_at',
            ]);

        $payload = $events->map(function (SensorEvent $event) use ($from) {
            $ts = $event->occurred_at ?? $event->created_at;

            $tsCarbon = $ts ? Carbon::parse($ts) : null;

            return [
                'id' => $event->id,
                'occurred_at' => $tsCarbon ? $tsCarbon->toIso8601String() : null,
                'seconds' => $tsCarbon ? $from->diffInSeconds($tsCarbon) : null,
            ];
        })->values();

        return response()->json([
            'device_id' => $deviceId,
            'day' => $date->format('Y-m-d'),
            'count' => $payload->count(),
            'events' => $payload,
        ]);
    }
}
