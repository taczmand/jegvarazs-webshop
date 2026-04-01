<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleKmController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        if (!$user) {
            abort(403);
        }

        $vehicles = $user->vehicles()
            ->select(['vehicles.id', 'vehicles.license_plate', 'vehicles.current_odometer'])
            ->orderBy('license_plate', 'asc')
            ->get();

        return view('admin.vehicles.km_entry', [
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user) {
            return response()->json(['message' => 'Nincs jogosultság.'], 403);
        }

        $validated = $request->validate([
            'kms' => 'required|array',
            'kms.*' => 'nullable|integer|min:0|max:10000000',
        ]);

        $kms = $validated['kms'] ?? [];

        $vehicleIds = collect(array_keys($kms))
            ->map(fn ($id) => is_numeric($id) ? (int) $id : null)
            ->filter(fn ($id) => $id !== null)
            ->values()
            ->all();

        $assignedVehicleIds = $user->vehicles()->pluck('vehicles.id')->map(fn ($v) => (int) $v)->all();
        $allowed = array_flip($assignedVehicleIds);

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $today = now()->toDateString();

        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($vehicleIds as $vehicleId) {
                if (!isset($allowed[$vehicleId])) {
                    continue;
                }

                $value = $kms[(string) $vehicleId] ?? $kms[$vehicleId] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $vehicle = Vehicle::query()->select(['id', 'current_odometer'])->findOrFail($vehicleId);

                $latestOdometerEvent = VehicleEvent::query()
                    ->where('vehicle_id', $vehicle->id)
                    ->where('type', 'odometer')
                    ->whereNotNull('value')
                    ->whereNotNull('event_date')
                    ->orderBy('event_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first(['value']);

                $baseline = null;
                if ($vehicle->current_odometer !== null) {
                    $baseline = (int) $vehicle->current_odometer;
                }
                if ($latestOdometerEvent && is_numeric($latestOdometerEvent->value)) {
                    $ev = (int) $latestOdometerEvent->value;
                    $baseline = $baseline === null ? $ev : max($baseline, $ev);
                }

                if ($baseline !== null && (int) $value < (int) $baseline) {
                    $errors["kms.{$vehicleId}"] = ["A megadott km óra állás nem lehet kevesebb a jelenleginél ({$baseline})."]; 
                    continue;
                }

                $existingThisMonth = VehicleEvent::query()
                    ->where('vehicle_id', $vehicle->id)
                    ->where('type', 'odometer')
                    ->whereBetween('event_date', [$start, $end])
                    ->orderBy('event_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($existingThisMonth) {
                    $existingThisMonth->update([
                        'event_date' => $today,
                        'value' => (string) ((int) $value),
                        'note' => 'Havi kötelező rögzítés',
                    ]);
                } else {
                    VehicleEvent::create([
                        'vehicle_id' => $vehicle->id,
                        'type' => 'odometer',
                        'event_date' => $today,
                        'value' => (string) ((int) $value),
                        'note' => 'Havi kötelező rögzítés',
                    ]);
                }

                $vehicle->update([
                    'current_odometer' => (int) $value,
                ]);
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Validációs hiba.',
                    'errors' => $errors,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sikeres mentés!',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hiba történt: ' . $e->getMessage(),
            ], 500);
        }
    }
}
