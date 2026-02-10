<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
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

        $defaultAddressesByClientId = ClientAddress::query()
            ->whereIn('client_id', $clientIds)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get()
            ->groupBy('client_id')
            ->map(fn ($rows) => $rows->first());

        $payload = $clients->map(function ($client) use ($defaultAddressesByClientId) {
            $address = $defaultAddressesByClientId[$client->id] ?? null;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $address ? [
                    'country' => $address->country,
                    'zip_code' => $address->zip_code,
                    'city' => $address->city,
                    'address_line' => $address->address_line,
                ] : null,
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
