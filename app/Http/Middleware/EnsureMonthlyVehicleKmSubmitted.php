<?php

namespace App\Http\Middleware;

use App\Models\BasicData;
use App\Models\VehicleEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMonthlyVehicleKmSubmitted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return $next($request);
        }

        if ($request->routeIs('admin.vehicles.km.entry') || $request->routeIs('admin.vehicles.km.store') || $request->routeIs('admin.logout')) {
            return $next($request);
        }

        $requiredDay = BasicData::getVehicleKmRequiredDay();
        $today = now()->startOfDay();

        if ((int) $today->format('j') < (int) $requiredDay) {
            return $next($request);
        }

        $assignedVehicleIds = $user->vehicles()->pluck('vehicles.id')->map(fn ($v) => (int) $v)->all();
        if (count($assignedVehicleIds) === 0) {
            return $next($request);
        }

        $start = $today->copy()->startOfMonth()->toDateString();
        $end = $today->copy()->endOfMonth()->toDateString();

        $submittedVehicleIds = VehicleEvent::query()
            ->whereIn('vehicle_id', $assignedVehicleIds)
            ->where('type', 'odometer')
            ->whereBetween('event_date', [$start, $end])
            ->distinct()
            ->pluck('vehicle_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $submitted = array_flip($submittedVehicleIds);
        foreach ($assignedVehicleIds as $vid) {
            if (!isset($submitted[$vid])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Havi km rögzítés esedékes.',
                        'redirect_url' => route('admin.vehicles.km.entry'),
                    ], 409);
                }

                return redirect()->route('admin.vehicles.km.entry');
            }
        }

        return $next($request);
    }
}
