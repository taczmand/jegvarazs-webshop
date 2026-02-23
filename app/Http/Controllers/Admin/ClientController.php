<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Worksheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['clients' => []]);
        }

        $clients = Client::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $clientIds = $clients->pluck('id')->toArray();

        $addressesByClientId = ClientAddress::query()
            ->whereIn('client_id', $clientIds)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get()
            ->groupBy('client_id');

        $payload = $clients->map(function ($client) use ($addressesByClientId) {
            $addresses = $addressesByClientId[$client->id] ?? collect();

            return [
                'id' => $client->id,
                'name' => $client->name,
                'id_number' => $client->id_number,
                'email' => $client->email,
                'phone' => $client->phone,
                'addresses' => $addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'is_default' => (bool) $address->is_default,
                        'country' => $address->country,
                        'zip_code' => $address->zip_code,
                        'city' => $address->city,
                        'address_line' => $address->address_line,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'clients' => $payload,
        ]);
    }

    public function index()
    {
        return view('admin.business.clients');
    }

    public function data()
    {
        $clients = Client::select([
            'id',
            'name',
            'mothers_name',
            'place_of_birth',
            'date_of_birth',
            'id_number',
            'email',
            'phone',
            'comment',
            'created_at as created',
            'updated_at as updated',
        ]);

        return DataTables::of($clients)
            ->addColumn('action', function ($client) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-client')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $client->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('edit-client')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-secondary timeline" data-id="' . $client->id . '" title="Megtekintés">
                            <i class="fas fa-eye"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-client')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $client->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function timeline($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-client')) {
            return response()->json(['message' => 'Nincs jogosultságod megtekinteni.'], 403);
        }

        $client = Client::query()->select(['id', 'name', 'email', 'phone'])->findOrFail($id);

        $formatMaybeDateOnly = function ($value) {
            if (is_null($value) || $value === '') {
                return '';
            }

            $raw = (string) $value;
            $isDateOnly = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1;
            $carbon = Carbon::parse($raw);

            return $isDateOnly
                ? $carbon->format('Y-m-d')
                : $carbon->format('Y-m-d H:i:s');
        };

        $items = collect();

        $contracts = Contract::query()
            ->where('client_id', $id)
            ->select(['id', 'installation_date', 'created_at', 'updated_at', 'email', 'phone', 'data'])
            ->with(['products' => function ($q) {
                $q->select(['products.id', 'title']);
            }])
            ->get()
            ->map(function ($contract) use ($formatMaybeDateOnly) {
                $date = $contract->installation_date
                    ? Carbon::parse($contract->installation_date)
                    : Carbon::parse($contract->created_at);

                $installationDateFormatted = $contract->installation_date ? $formatMaybeDateOnly($contract->installation_date) : '';

                $note = '';
                $data = is_array($contract->data) ? $contract->data : (array) $contract->data;
                foreach (['comment', 'description', 'note', 'megjegyzes', 'megjegyzés'] as $key) {
                    $value = $data[$key] ?? '';
                    if (is_string($value) && trim($value) !== '') {
                        $note = trim($value);
                        break;
                    }
                }

                $products = $contract->products
                    ? $contract->products->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'title' => (string) ($p->title ?? ''),
                            'qty' => (int) ($p->pivot->product_qty ?? 0),
                            'gross_price' => $p->pivot->gross_price ?? null,
                        ];
                    })->values()
                    : collect();

                $lines = [
                    ['label' => 'Azonosító', 'value' => '#' . $contract->id],
                    ['label' => 'Telepítés dátuma', 'value' => $installationDateFormatted],
                    ['label' => 'E-mail', 'value' => (string) ($contract->email ?? '')],
                    ['label' => 'Telefon', 'value' => (string) ($contract->phone ?? '')],
                    ['label' => 'Létrehozva', 'value' => $contract->created_at ? Carbon::parse($contract->created_at)->format('Y-m-d H:i:s') : ''],
                    ['label' => 'Módosítva', 'value' => $contract->updated_at ? Carbon::parse($contract->updated_at)->format('Y-m-d H:i:s') : ''],
                ];

                return [
                    'type' => 'contract',
                    'title' => 'Szerződés #' . $contract->id,
                    'date' => $contract->installation_date ? $installationDateFormatted : ($date ? $date->format('Y-m-d H:i:s') : null),
                    'timestamp' => $date ? $date->timestamp : 0,
                    'url' => route('admin.contracts.index', ['id' => $contract->id, 'modal' => true]),
                    'lines' => $lines,
                    'note' => $note,
                    'products' => $products,
                ];
            });

        $worksheets = Worksheet::query()
            ->where('client_id', $id)
            ->select(['id', 'work_type', 'installation_date', 'description', 'worker_report', 'created_at', 'updated_at'])
            ->with(['products' => function ($q) {
                $q->select(['products.id', 'title']);
            }])
            ->get()
            ->map(function ($worksheet) use ($formatMaybeDateOnly) {
                $date = $worksheet->installation_date
                    ? Carbon::parse($worksheet->installation_date)
                    : Carbon::parse($worksheet->created_at);

                $installationDateFormatted = $worksheet->installation_date ? $formatMaybeDateOnly($worksheet->installation_date) : '';

                $typeLabel = $worksheet->work_type ? (' (' . $worksheet->work_type . ')') : '';

                $lines = [
                    ['label' => 'Azonosító', 'value' => '#' . $worksheet->id],
                    ['label' => 'Típus', 'value' => (string) ($worksheet->work_type ?? '')],
                    ['label' => 'Telepítés dátuma', 'value' => $installationDateFormatted],
                    ['label' => 'Létrehozva', 'value' => $worksheet->created_at ? Carbon::parse($worksheet->created_at)->format('Y-m-d H:i:s') : ''],
                    ['label' => 'Módosítva', 'value' => $worksheet->updated_at ? Carbon::parse($worksheet->updated_at)->format('Y-m-d H:i:s') : ''],
                ];

                $noteParts = [];
                if (is_string($worksheet->description ?? null) && trim($worksheet->description) !== '') {
                    $noteParts[] = trim($worksheet->description);
                }
                if (is_string($worksheet->worker_report ?? null) && trim($worksheet->worker_report) !== '') {
                    $noteParts[] = 'Dolgozói jelentés: ' . trim($worksheet->worker_report);
                }
                $note = trim(implode("\n", $noteParts));

                $products = $worksheet->products
                    ? $worksheet->products->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'title' => (string) ($p->title ?? ''),
                            'qty' => (int) ($p->pivot->quantity ?? 0),
                            'gross_price' => null,
                        ];
                    })->values()
                    : collect();

                return [
                    'type' => 'worksheet',
                    'title' => 'Munkalap #' . $worksheet->id . $typeLabel,
                    'date' => $worksheet->installation_date ? $installationDateFormatted : ($date ? $date->format('Y-m-d H:i:s') : null),
                    'timestamp' => $date ? $date->timestamp : 0,
                    'url' => route('admin.worksheets.index', ['id' => $worksheet->id]),
                    'lines' => $lines,
                    'note' => $note,
                    'products' => $products,
                ];
            });

        $appointments = Appointment::query()
            ->where('client_id', $id)
            ->select(['id', 'appointment_date', 'created_at', 'updated_at'])
            ->get()
            ->map(function ($appointment) {
                $date = $appointment->appointment_date
                    ? Carbon::parse($appointment->appointment_date)
                    : Carbon::parse($appointment->created_at);

                $lines = [
                    ['label' => 'Azonosító', 'value' => '#' . $appointment->id],
                    ['label' => 'Időpont', 'value' => $appointment->appointment_date ? Carbon::parse($appointment->appointment_date)->format('Y-m-d H:i:s') : ''],
                    ['label' => 'Létrehozva', 'value' => $appointment->created_at ? Carbon::parse($appointment->created_at)->format('Y-m-d H:i:s') : ''],
                    ['label' => 'Módosítva', 'value' => $appointment->updated_at ? Carbon::parse($appointment->updated_at)->format('Y-m-d H:i:s') : ''],
                ];

                return [
                    'type' => 'appointment',
                    'title' => 'Időpontfoglalás #' . $appointment->id,
                    'date' => $date ? $date->format('Y-m-d H:i:s') : null,
                    'timestamp' => $date ? $date->timestamp : 0,
                    'url' => route('admin.appointments.index', ['id' => $appointment->id]),
                    'lines' => $lines,
                ];
            });

        $offers = Offer::query()
            ->where('client_id', $id)
            ->select(['id', 'title', 'name', 'email', 'phone', 'country', 'zip_code', 'city', 'address_line', 'description', 'created_at', 'updated_at'])
            ->with(['products' => function ($q) {
                $q->select(['products.id', 'title']);
            }])
            ->get()
            ->map(function ($offer) {
                $date = $offer->created_at ? Carbon::parse($offer->created_at) : null;

                $lines = [
                    ['label' => 'Azonosító', 'value' => '#' . $offer->id],
                    ['label' => 'Cím', 'value' => (string) ($offer->title ?? '')],
                    ['label' => 'Név', 'value' => (string) ($offer->name ?? '')],
                    ['label' => 'E-mail', 'value' => (string) ($offer->email ?? '')],
                    ['label' => 'Telefon', 'value' => (string) ($offer->phone ?? '')],
                    ['label' => 'Ország', 'value' => (string) ($offer->country ?? '')],
                    ['label' => 'Irányítószám', 'value' => (string) ($offer->zip_code ?? '')],
                    ['label' => 'Város', 'value' => (string) ($offer->city ?? '')],
                    ['label' => 'Cím', 'value' => (string) ($offer->address_line ?? '')],
                    ['label' => 'Létrehozva', 'value' => $offer->created_at ? Carbon::parse($offer->created_at)->format('Y-m-d H:i:s') : ''],
                    ['label' => 'Módosítva', 'value' => $offer->updated_at ? Carbon::parse($offer->updated_at)->format('Y-m-d H:i:s') : ''],
                ];

                $products = $offer->products
                    ? $offer->products->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'title' => (string) ($p->title ?? ''),
                            'qty' => (int) ($p->pivot->quantity ?? 0),
                            'gross_price' => $p->pivot->gross_price ?? null,
                        ];
                    })->values()
                    : collect();

                return [
                    'type' => 'offer',
                    'title' => 'Ajánlat #' . $offer->id,
                    'date' => $date ? $date->format('Y-m-d H:i:s') : null,
                    'timestamp' => $date ? $date->timestamp : 0,
                    'url' => route('admin.offers.index', ['id' => $offer->id, 'modal' => true]),
                    'lines' => $lines,
                    'note' => (string) ($offer->description ?? ''),
                    'products' => $products,
                ];
            });

        $items = $items
            ->merge($contracts)
            ->merge($worksheets)
            ->merge($appointments)
            ->merge($offers)
            ->sortByDesc('timestamp')
            ->values();

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
            ],
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'mothers_name' => 'nullable|string|max:255',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'id_number' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'comment' => 'nullable|string',

            'address_country' => 'required|string|max:10',
            'address_zip_code' => 'required|string|max:20',
            'address_city' => 'required|string|max:100',
            'address_address_line' => 'required|string|max:255',
            'address_comment' => 'nullable|string',
        ]);

        try {
            $existing = Client::where('email', $request->input('email'))->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Ezzel az e-mail címmel már létezik ügyfél.',
                    'errors' => [
                        'email' => ['Ezzel az e-mail címmel már létezik ügyfél.'],
                    ],
                ], 422);
            }

            $client = Client::create([
                'name' => $request->input('name') ?: null,
                'mothers_name' => $request->input('mothers_name') ?: null,
                'place_of_birth' => $request->input('place_of_birth') ?: null,
                'date_of_birth' => $request->input('date_of_birth') ?: null,
                'id_number' => $request->input('id_number') ?: null,
                'email' => $request->input('email'),
                'phone' => $request->input('phone') ?: null,
                'comment' => $request->input('comment') ?: null,
            ]);

            ClientAddress::create([
                'client_id' => $client->id,
                'country' => $request->input('address_country') ?: 'HU',
                'zip_code' => $request->input('address_zip_code') ?: null,
                'city' => $request->input('address_city') ?: null,
                'address_line' => $request->input('address_address_line') ?: null,
                'comment' => $request->input('address_comment') ?: null,
                'is_default' => true,
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'client' => $client,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ügyfél mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'mothers_name' => 'nullable|string|max:255',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'id_number' => 'nullable|string|max:50',
            'email' => 'required|email|max:255|unique:clients,email,' . $request->input('id'),
            'phone' => 'nullable|string|max:50',
            'comment' => 'nullable|string',
        ]);

        try {
            $client = Client::findOrFail($request->input('id'));

            $client->update([
                'name' => $request->input('name') ?: null,
                'mothers_name' => $request->input('mothers_name') ?: null,
                'place_of_birth' => $request->input('place_of_birth') ?: null,
                'date_of_birth' => $request->input('date_of_birth') ?: null,
                'id_number' => $request->input('id_number') ?: null,
                'email' => $request->input('email'),
                'phone' => $request->input('phone') ?: null,
                'comment' => $request->input('comment') ?: null,
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'client' => $client,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ügyfél mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $client = Client::findOrFail($request->id);
            $client->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ügyfél törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function addresses($id)
    {
        $client = Client::findOrFail($id);

        return response()->json([
            'addresses' => $client->addresses()->orderByDesc('is_default')->orderBy('id')->get(),
        ]);
    }

    public function storeAddress(Request $request, $id)
    {
        $request->validate([
            'country' => 'nullable|string|max:10',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'address_line' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            $client = Client::findOrFail($id);
            $isDefault = (bool) $request->input('is_default');

            if ($isDefault) {
                ClientAddress::where('client_id', $client->id)->update(['is_default' => false]);
            }

            $address = ClientAddress::create([
                'client_id' => $client->id,
                'country' => $request->input('country') ?: 'HU',
                'zip_code' => $request->input('zip_code') ?: null,
                'city' => $request->input('city') ?: null,
                'address_line' => $request->input('address_line') ?: null,
                'comment' => $request->input('comment') ?: null,
                'is_default' => $isDefault,
            ]);

            return response()->json([
                'message' => 'Cím sikeresen mentve!',
                'address' => $address,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ügyfél cím mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a cím mentése során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAddress(Request $request, $addressId)
    {
        $request->validate([
            'country' => 'nullable|string|max:10',
            'zip_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'address_line' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            $address = ClientAddress::findOrFail($addressId);
            $isDefault = (bool) $request->input('is_default');

            if ($isDefault) {
                ClientAddress::where('client_id', $address->client_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'country' => $request->input('country') ?: 'HU',
                'zip_code' => $request->input('zip_code') ?: null,
                'city' => $request->input('city') ?: null,
                'address_line' => $request->input('address_line') ?: null,
                'comment' => $request->input('comment') ?: null,
                'is_default' => $isDefault,
            ]);

            return response()->json([
                'message' => 'Cím sikeresen mentve!',
                'address' => $address,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ügyfél cím mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a cím mentése során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAddress($addressId)
    {
        try {
            $address = ClientAddress::findOrFail($addressId);
            $address->delete();

            return response()->json([
                'message' => 'Cím sikeresen törölve!',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Ügyfél cím törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a cím törlése során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
