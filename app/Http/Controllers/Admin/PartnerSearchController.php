<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Customer;
use App\Models\CustomerBillingAddress;
use Illuminate\Http\Request;

class PartnerSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['partners' => []]);
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

        $customers = Customer::query()
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(10)
            ->get();

        $clientIds = $clients->pluck('id')->toArray();
        $customerIds = $customers->pluck('id')->toArray();

        $addressesByClientId = ClientAddress::query()
            ->whereIn('client_id', $clientIds)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get()
            ->groupBy('client_id');

        $billingAddressesByCustomerId = CustomerBillingAddress::query()
            ->whereIn('customer_id', $customerIds)
            ->orderByDesc('id')
            ->get()
            ->groupBy('customer_id');

        $clientPayload = $clients->map(function ($client) use ($addressesByClientId) {
            $addresses = $addressesByClientId[$client->id] ?? collect();

            return [
                'source' => 'client',
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
        });

        $customerPayload = $customers->map(function ($customer) use ($billingAddressesByCustomerId) {
            $addresses = $billingAddressesByCustomerId[$customer->id] ?? collect();

            $name = trim((string) ($customer->last_name . ' ' . $customer->first_name));

            return [
                'source' => 'customer',
                'id' => $customer->id,
                'name' => $name,
                'id_number' => null,
                'email' => $customer->email,
                'phone' => null,
                'addresses' => $addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'is_default' => false,
                        'country' => $address->country,
                        'zip_code' => $address->zip_code,
                        'city' => $address->city,
                        'address_line' => $address->address_line,
                    ];
                })->values(),
            ];
        });

        $payload = $clientPayload
            ->concat($customerPayload)
            ->sortBy(function ($row) {
                return mb_strtolower((string) ($row['name'] ?? ''));
            })
            ->values()
            ->take(10)
            ->values();

        return response()->json([
            'partners' => $payload,
        ]);
    }
}
