<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleEvent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    public function index()
    {
        return view('admin.vehicles.vehicles');
    }

    public function data()
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-vehicles')) {
            return response()->json([
                'message' => 'Nincs jogosultságod a járművek megtekintésére.',
            ], 403);
        }

        $items = Vehicle::query()->select(['id', 'license_plate', 'type', 'status', 'technical_inspection_expires_at', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($items)
            ->addColumn('status', function ($row) {
                $translations = [
                    'active' => 'Aktív',
                    'inactive' => 'Inaktív',
                ];

                return $translations[$row->status] ?? ucfirst((string) $row->status);
            })
            ->editColumn('technical_inspection_expires_at', function ($row) {
                if (empty($row->technical_inspection_expires_at)) {
                    return '';
                }

                try {
                    return \Carbon\Carbon::parse($row->technical_inspection_expires_at)->format('Y-m-d');
                } catch (\Exception $e) {
                    return (string) $row->technical_inspection_expires_at;
                }
            })
            ->addColumn('action', function ($row) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-vehicle')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $row->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-vehicle')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $row->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-vehicle')) {
            return response()->json(['message' => 'Nincs jogosultságod jármű létrehozásához.'], 403);
        }

        $validated = $request->validate([
            'license_plate' => 'required|string|max:32|unique:vehicles,license_plate',
            'type' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'technical_inspection_expires_at' => 'nullable|date',
        ]);

        $vehicle = Vehicle::create([
            'license_plate' => $validated['license_plate'],
            'type' => $validated['type'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'technical_inspection_expires_at' => $validated['technical_inspection_expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Sikeres mentés!',
            'vehicle' => $vehicle,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-vehicle')) {
            return response()->json(['message' => 'Nincs jogosultságod jármű szerkesztéséhez.'], 403);
        }

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'license_plate' => 'required|string|max:32|unique:vehicles,license_plate,' . $vehicle->id,
            'type' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'technical_inspection_expires_at' => 'nullable|date',
        ]);

        $vehicle->update([
            'license_plate' => $validated['license_plate'],
            'type' => $validated['type'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'technical_inspection_expires_at' => $validated['technical_inspection_expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Sikeres mentés!',
            'vehicle' => $vehicle,
        ], 200);
    }

    public function destroy($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-vehicle')) {
            return response()->json(['message' => 'Nincs jogosultságod jármű törléséhez.'], 403);
        }

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'message' => 'Sikeres törlés!',
        ], 200);
    }

    public function eventsData($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-vehicle-events')) {
            return response()->json([
                'message' => 'Nincs jogosultságod az események megtekintésére.',
            ], 403);
        }

        $vehicle = Vehicle::findOrFail($id);

        $items = VehicleEvent::query()
            ->where('vehicle_id', $vehicle->id)
            ->select(['id', 'vehicle_id', 'type', 'event_date', 'value', 'note', 'created_at'])
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc');

        return DataTables::of($items)
            ->editColumn('type', function ($row) {
                $map = [
                    'oil_change' => 'Olajcsere',
                    'odometer' => 'Km óra állás',
                    'monthly_odometer' => 'Havi km óra állás',
                ];
                $t = (string) ($row->type ?? '');
                return $map[$t] ?? $t;
            })
            ->editColumn('event_date', function ($row) {
                if (empty($row->event_date)) {
                    return '';
                }

                try {
                    return \Carbon\Carbon::parse($row->event_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    return (string) $row->event_date;
                }
            })
            ->addColumn('action', function ($row) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('delete-vehicle-event')) {
                    $buttons .= '
                        <button type="button" class="btn btn-sm btn-danger delete-event" data-id="' . $row->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeEvent(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-vehicle-event')) {
            return response()->json(['message' => 'Nincs jogosultságod esemény rögzítéséhez.'], 403);
        }

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|string|max:64',
            'event_date' => 'required|date',
            'value' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        $event = VehicleEvent::create([
            'vehicle_id' => $vehicle->id,
            'type' => $validated['type'],
            'event_date' => $validated['event_date'],
            'value' => $validated['value'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'message' => 'Esemény rögzítve!',
            'event' => $event,
        ], 200);
    }

    public function destroyEvent($vehicleId, $eventId)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-vehicle-event')) {
            return response()->json(['message' => 'Nincs jogosultságod esemény törléséhez.'], 403);
        }

        $vehicle = Vehicle::findOrFail($vehicleId);
        $event = VehicleEvent::where('vehicle_id', $vehicle->id)->findOrFail($eventId);
        $event->delete();

        return response()->json([
            'message' => 'Sikeres törlés!',
        ], 200);
    }
}
