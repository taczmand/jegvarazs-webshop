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

    public function kmSummary(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-vehicles')) {
            return response()->json([
                'message' => 'Nincs jogosultságod a járművek megtekintésére.',
            ], 403);
        }

        $vehicle = Vehicle::findOrFail($id);

        $availableYears = VehicleEvent::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', 'odometer')
            ->whereNotNull('event_date')
            ->selectRaw('DISTINCT YEAR(event_date) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values()
            ->all();

        $defaultYear = (int) now()->format('Y');
        $year = (int) $request->query('year', $defaultYear);
        if ($year < 1970 || $year > ($defaultYear + 1)) {
            $year = $defaultYear;
        }

        $events = VehicleEvent::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', 'odometer')
            ->whereYear('event_date', $year)
            ->whereNotNull('value')
            ->whereNotNull('event_date')
            ->orderBy('event_date', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'event_date', 'value']);

        $baseline = VehicleEvent::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', 'odometer')
            ->whereNotNull('value')
            ->whereNotNull('event_date')
            ->whereDate('event_date', '<', sprintf('%04d-01-01', $year))
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->first(['event_date', 'value']);

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Már', 4 => 'Ápr', 5 => 'Máj', 6 => 'Jún',
            7 => 'Júl', 8 => 'Aug', 9 => 'Szep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec'
        ];

        $kmByMonth = array_fill(1, 12, 0);
        $lastOdo = null;
        $lastDate = null;

        if ($baseline) {
            try {
                $lastDate = \Carbon\Carbon::parse($baseline->event_date)->startOfDay();
                $lastOdo = is_numeric($baseline->value) ? (int) $baseline->value : null;
            } catch (\Exception $e) {
                $lastOdo = null;
                $lastDate = null;
            }
        }

        foreach ($events as $ev) {
            try {
                $d = \Carbon\Carbon::parse($ev->event_date)->startOfDay();
            } catch (\Exception $e) {
                continue;
            }

            $odo = is_numeric($ev->value) ? (int) $ev->value : null;
            if ($odo === null) {
                continue;
            }

            if ($lastOdo !== null && $lastDate !== null) {
                $delta = $odo - $lastOdo;
                if ($delta >= 0) {
                    $m = (int) $d->format('n');
                    if ($m >= 1 && $m <= 12) {
                        $kmByMonth[$m] += $delta;
                    }
                }
            }

            $lastOdo = $odo;
            $lastDate = $d;
        }

        $dataPoints = [];
        foreach ($kmByMonth as $m => $km) {
            $dataPoints[] = [
                'label' => $months[$m] ?? (string) $m,
                'y' => (int) $km,
            ];
        }

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
            ],
            'year' => $year,
            'availableYears' => $availableYears,
            'dataPoints' => $dataPoints,
        ], 200);
    }

    public function timeline($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-vehicles')) {
            return response()->json([
                'message' => 'Nincs jogosultságod a járművek megtekintésére.',
            ], 403);
        }

        $vehicle = Vehicle::query()->select([
            'id',
            'license_plate',
            'type',
            'status',
            'technical_inspection_expires_at',
            'note',
            'current_odometer',
            'last_oil_change_odometer',
            'oil_change_interval',
            'created_at',
            'updated_at',
        ])
            ->with(['users:id'])
            ->findOrFail($id);

        $events = VehicleEvent::query()
            ->where('vehicle_id', $vehicle->id)
            ->select(['id', 'type', 'event_date', 'value', 'note', 'created_at'])
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $items = [];

        $interval = (int) ($vehicle->oil_change_interval ?? 12000);
        $remainingKm = null;
        $nextOilChangeAtOdometer = null;

        if ($vehicle->current_odometer !== null && $vehicle->last_oil_change_odometer !== null) {
            $distance = (int) $vehicle->current_odometer - (int) $vehicle->last_oil_change_odometer;
            $remainingKm = $interval - $distance;
            $nextOilChangeAtOdometer = (int) $vehicle->last_oil_change_odometer + $interval;
        }

        $inspectionItem = null;
        if (!empty($vehicle->technical_inspection_expires_at)) {
            try {
                $inspectionDate = \Carbon\Carbon::parse($vehicle->technical_inspection_expires_at);
                $today = \Carbon\Carbon::today();

                $status = 'future';
                if ($inspectionDate->isSameDay($today)) {
                    $status = 'today';
                } elseif ($inspectionDate->isBefore($today)) {
                    $status = 'expired';
                }

                $title = $status === 'expired'
                    ? 'Műszaki vizsga lejárt'
                    : 'Műszaki vizsga lejár';

                $inspectionItem = [
                    'kind' => 'future',
                    'type' => 'technical_inspection',
                    'title' => $title,
                    'date' => $inspectionDate->format('Y-m-d'),
                    'meta' => [
                        'status' => $status,
                    ],
                ];
            } catch (\Exception $e) {
                $inspectionItem = [
                    'kind' => 'future',
                    'type' => 'technical_inspection',
                    'title' => 'Műszaki vizsga lejár',
                    'date' => (string) $vehicle->technical_inspection_expires_at,
                    'meta' => [
                        'status' => null,
                    ],
                ];
            }
        }

        if ($inspectionItem && (($inspectionItem['meta']['status'] ?? null) !== 'expired')) {
            $items[] = $inspectionItem;
        }

        $items[] = [
            'kind' => 'present',
            'title' => 'Jelen',
            'date' => null,
            'meta' => [
                'current_odometer' => $vehicle->current_odometer,
                'oil_change_remaining_km' => $remainingKm,
                'next_oil_change_at_odometer' => $nextOilChangeAtOdometer,
            ],
        ];

        if ($inspectionItem && (($inspectionItem['meta']['status'] ?? null) === 'expired')) {
            $items[] = $inspectionItem;
        }

        foreach ($events as $event) {
            $items[] = [
                'kind' => 'event',
                'id' => $event->id,
                'type' => $event->type,
                'title' => $event->type === 'oil_change' ? 'Olajcsere' : ($event->type === 'odometer' ? 'Km óra állás' : (string) $event->type),
                'date' => !empty($event->event_date) ? (string) \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') : null,
                'meta' => [
                    'value' => $event->value,
                    'note' => $event->note,
                ],
            ];
        }

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
                'type' => $vehicle->type,
                'status' => $vehicle->status,
                'technical_inspection_expires_at' => $vehicle->technical_inspection_expires_at ? (string) $vehicle->technical_inspection_expires_at : null,
                'current_odometer' => $vehicle->current_odometer,
                'last_oil_change_odometer' => $vehicle->last_oil_change_odometer,
                'oil_change_interval' => $interval,
                'assigned_user_ids' => $vehicle->users ? $vehicle->users->pluck('id')->values()->all() : [],
            ],
            'items' => $items,
        ], 200);
    }

    public function data()
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-vehicles')) {
            return response()->json([
                'message' => 'Nincs jogosultságod a járművek megtekintésére.',
            ], 403);
        }

        $items = Vehicle::query()->select([
            'id',
            'license_plate',
            'type',
            'status',
            'technical_inspection_expires_at',
            'note',
            'current_odometer',
            'last_oil_change_odometer',
            'oil_change_interval',
            'created_at as created',
            'updated_at as updated'
        ])->with(['users:id']);

        return DataTables::of($items)
            ->addColumn('assigned_user_ids', function ($row) {
                try {
                    return $row->users ? $row->users->pluck('id')->values()->all() : [];
                } catch (\Throwable $e) {
                    return [];
                }
            })
            ->addColumn('status', function ($row) {
                $translations = [
                    'active' => 'Aktív',
                    'inactive' => 'Inaktív',
                ];

                return $translations[$row->status] ?? ucfirst((string) $row->status);
            })
            ->addColumn('attention', function ($row) {
                $badges = [];

                $oilWarning = false;
                if ($row->current_odometer !== null && $row->last_oil_change_odometer !== null) {
                    $interval = (int) ($row->oil_change_interval ?? 12000);
                    $distance = (int) $row->current_odometer - (int) $row->last_oil_change_odometer;
                    if ($interval > 0 && $distance >= ($interval * 0.8)) {
                        $oilWarning = true;
                    }
                }

                if ($oilWarning) {
                    $badges[] = '<span class="badge bg-warning text-dark">Olajcsere</span>';
                }

                $techWarning = false;
                $techBadge = null;
                if (!empty($row->technical_inspection_expires_at)) {
                    try {
                        $expires = \Carbon\Carbon::parse($row->technical_inspection_expires_at)->startOfDay();
                        $today = \Carbon\Carbon::today();

                        $totalDays = 730;
                        $thresholdDays = (int) ceil($totalDays * 0.2);
                        $deadline = $today->copy()->addDays($thresholdDays);

                        if ($expires->isBefore($today)) {
                            $techWarning = true;
                            $techBadge = '<span class="badge bg-danger">Műszaki lejárt</span>';
                        } elseif ($expires->isSameDay($today)) {
                            $techWarning = true;
                            $techBadge = '<span class="badge bg-secondary">Műszaki ma</span>';
                        } elseif ($expires->lessThanOrEqualTo($deadline)) {
                            $techWarning = true;
                            $techBadge = '<span class="badge bg-success">Műszaki</span>';
                        }
                    } catch (\Exception $e) {
                        $techWarning = false;
                    }
                }

                if ($techWarning && $techBadge) {
                    $badges[] = $techBadge;
                }

                if (count($badges) === 0) {
                    return '';
                }

                return '<div class="d-flex gap-1 flex-wrap">' . implode('', $badges) . '</div>';
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
            ->addColumn('technical_inspection_remaining_days', function ($row) {
                if (empty($row->technical_inspection_expires_at)) {
                    return '';
                }

                try {
                    $expires = \Carbon\Carbon::parse($row->technical_inspection_expires_at)->startOfDay();
                    $today = \Carbon\Carbon::today();
                    return (string) $today->diffInDays($expires, false);
                } catch (\Exception $e) {
                    return '';
                }
            })
            ->addColumn('oil_change_remaining_km', function ($row) {
                if ($row->current_odometer === null || $row->last_oil_change_odometer === null) {
                    return '';
                }

                $interval = (int) ($row->oil_change_interval ?? 12000);
                $distance = (int) $row->current_odometer - (int) $row->last_oil_change_odometer;
                $remaining = $interval - $distance;

                return (string) $remaining;
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
            ->rawColumns(['attention', 'action'])
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
            'note' => 'nullable|string|max:20000',
            'oil_change_interval' => 'nullable|integer|min:1|max:1000000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $vehicle = Vehicle::create([
            'license_plate' => $validated['license_plate'],
            'type' => $validated['type'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'technical_inspection_expires_at' => $validated['technical_inspection_expires_at'] ?? null,
            'note' => $validated['note'] ?? null,
            'oil_change_interval' => $validated['oil_change_interval'] ?? 12000,
        ]);

        if (isset($validated['user_ids']) && is_array($validated['user_ids'])) {
            $vehicle->users()->sync($validated['user_ids']);
        }

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
            'note' => 'nullable|string|max:20000',
            'oil_change_interval' => 'nullable|integer|min:1|max:1000000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $vehicle->update([
            'license_plate' => $validated['license_plate'],
            'type' => $validated['type'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'technical_inspection_expires_at' => $validated['technical_inspection_expires_at'] ?? null,
            'note' => $validated['note'] ?? null,
            'oil_change_interval' => $validated['oil_change_interval'] ?? ($vehicle->oil_change_interval ?? 12000),
        ]);

        if (array_key_exists('user_ids', $validated) && is_array($validated['user_ids'])) {
            $vehicle->users()->sync($validated['user_ids']);
        }

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

        $type = (string) $validated['type'];

        if ($type === 'odometer') {
            $request->validate([
                'value' => 'required|integer|min:0|max:10000000',
            ]);
        }

        $event = VehicleEvent::create([
            'vehicle_id' => $vehicle->id,
            'type' => $type,
            'event_date' => $validated['event_date'],
            'value' => $validated['value'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        if (in_array($type, ['odometer', 'monthly_odometer'], true)) {
            $latestOdometerEvent = VehicleEvent::query()
                ->where('vehicle_id', $vehicle->id)
                ->whereIn('type', ['odometer', 'monthly_odometer'])
                ->orderBy('event_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestOdometerEvent && $latestOdometerEvent->value !== null && $latestOdometerEvent->value !== '') {
                $vehicle->update([
                    'current_odometer' => (int) $latestOdometerEvent->value,
                ]);
            }
        }

        if ($type === 'oil_change') {
            if ($vehicle->current_odometer !== null) {
                $vehicle->update([
                    'last_oil_change_odometer' => (int) $vehicle->current_odometer,
                ]);
            }
        }

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
        $deletedType = (string) $event->type;
        $event->delete();

        if (in_array($deletedType, ['odometer', 'monthly_odometer'], true)) {
            $latestOdometerEvent = VehicleEvent::query()
                ->where('vehicle_id', $vehicle->id)
                ->whereIn('type', ['odometer', 'monthly_odometer'])
                ->orderBy('event_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $vehicle->update([
                'current_odometer' => $latestOdometerEvent && $latestOdometerEvent->value !== null && $latestOdometerEvent->value !== ''
                    ? (int) $latestOdometerEvent->value
                    : null,
            ]);
        }

        return response()->json([
            'message' => 'Sikeres törlés!',
        ], 200);
    }
}
