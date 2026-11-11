<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerBillingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PartnerSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['partners' => []]);
        }

        $words = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY);

        $clients = Client::query()
            ->where(function ($query) use ($q, $words) {
                $query->where(function ($nameQuery) use ($words) {
                    foreach ($words as $word) {
                        $nameQuery->where('name', 'like', "%{$word}%");
                    }
                })
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('addresses', function ($inner) use ($words) {
                        foreach ($words as $word) {
                            $inner->where('client_name', 'like', "%{$word}%");
                        }
                    });
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $customers = Customer::query()
            ->where(function ($query) use ($q, $words) {
                $query->where(function ($nameQuery) use ($words) {
                    foreach ($words as $word) {
                        $nameQuery->where(function ($wordQuery) use ($word) {
                            $wordQuery->where('first_name', 'like', "%{$word}%")
                                ->orWhere('last_name', 'like', "%{$word}%");
                        });
                    }
                })
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

            $taxNumber = $addresses
                ->pluck('tax_number')
                ->map(fn ($value) => trim((string) $value))
                ->first(fn ($value) => $value !== '');

            return [
                'source' => 'client',
                'id' => $client->id,
                'name' => $client->name,
                'id_number' => $client->id_number,
                'tax_number' => $taxNumber ?: null,
                'email' => $client->email,
                'phone' => $client->phone,
                'addresses' => $addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'is_default' => (bool) $address->is_default,
                        'client_name' => $address->client_name,
                        'tax_number' => $address->tax_number ?: null,
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

            $taxNumber = $addresses
                ->pluck('tax_number')
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->first(function ($value) {
                    return $value !== '';
                });

            $name = trim((string) ($customer->last_name . ' ' . $customer->first_name));

            return [
                'source' => 'customer',
                'id' => $customer->id,
                'name' => $name,
                'is_partner' => (bool) ($customer->is_partner ?? false),
                'id_number' => $taxNumber ?: null,
                'tax_number' => $taxNumber ?: null,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'addresses' => $addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'is_default' => false,
                        'client_name' => trim((string) $address->name) !== '' ? $address->name : null,
                        'billing_name' => $address->name,
                        'is_company' => (bool) $address->is_company,
                        'tax_number' => $address->tax_number ?: null,
                        'country' => $address->country,
                        'zip_code' => $address->zip_code,
                        'city' => $address->city,
                        'address_line' => $address->address_line,
                    ];
                })->values(),
            ];
        });

        $collator = class_exists(\Collator::class) ? new \Collator('hu_HU') : null;

        $payload = $clientPayload
            ->concat($customerPayload)
            ->sort(function ($a, $b) use ($collator) {
                $nameA = trim((string) ($a['name'] ?? ''));
                $nameB = trim((string) ($b['name'] ?? ''));

                if ($collator) {
                    return (int) $collator->compare($nameA, $nameB);
                }

                return strcmp(mb_strtolower($nameA), mb_strtolower($nameB));
            })
            ->values()
            ->take(10)
            ->values();

        return response()->json([
            'partners' => $payload,
        ]);
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string|in:client,customer',
            'partner_id' => 'required|integer',
            'address_id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:10',
            'zip_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'address_line' => 'required|string|max:255',
        ]);

        $partnerId = (int) $validated['partner_id'];
        $addressId = !empty($validated['address_id']) ? (int) $validated['address_id'] : null;
        $name = trim((string) ($validated['name'] ?? ''));
        $taxNumber = trim((string) ($validated['tax_number'] ?? ''));

        $data = [
            'tax_number' => $taxNumber !== '' ? $taxNumber : null,
            'country' => ($validated['country'] ?? '') ?: 'HU',
            'zip_code' => $validated['zip_code'],
            'city' => $validated['city'],
            'address_line' => $validated['address_line'],
        ];

        if ($validated['source'] === 'client') {
            $data['client_name'] = $name !== '' ? $name : null;

            if ($addressId) {
                $address = ClientAddress::query()->where('client_id', $partnerId)->findOrFail($addressId);
                $address->update($data);
            } else {
                $client = Client::query()->findOrFail($partnerId);
                $address = ClientAddress::create($data + ['client_id' => $client->id]);
            }
        } else {
            $data['name'] = $name !== '' ? $name : null;

            if ($addressId) {
                $address = CustomerBillingAddress::query()->where('customer_id', $partnerId)->findOrFail($addressId);
                $address->update($data);
            } else {
                $customer = Customer::query()->findOrFail($partnerId);
                $address = CustomerBillingAddress::create($data + ['customer_id' => $customer->id]);
            }
        }

        return response()->json([
            'message' => 'Cím sikeresen mentve!',
            'address' => $address,
        ], 200);
    }

    /**
     * Szamlazz.hu adózó-lekérdezés (NAV taxpayer query).
     * Csak 8 jegyű törzsszámra kereshető — név alapú keresést az API nem támogat.
     */
    public function taxpayer(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $digits = substr(preg_replace('/\D/', '', $q), 0, 8);

        if (strlen($digits) !== 8) {
            return response()->json(['taxpayers' => []]);
        }

        $companyId = (int) $request->query('company_id', 0);
        $company = $companyId
            ? Company::query()->where('status', 'active')->find($companyId)
            : null;
        if (!$company) {
            $company = Company::query()->where('status', 'active')->where('is_default', true)->first()
                ?? Company::query()->where('status', 'active')->whereNotNull('billing_provider_api_key')->first();
        }

        $apiKey = trim((string) ($company->billing_provider_api_key ?? ''));
        if ($apiKey === '') {
            return response()->json([
                'taxpayers' => [],
                'error' => 'Nincs beállítva számlázó API kulcs a kiválasztott céghez.',
            ]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<xmltaxpayer xmlns="http://www.szamlazz.hu/xmltaxpayer" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xsi:schemaLocation="http://www.szamlazz.hu/xmltaxpayer http://www.szamlazz.hu/docs/xsds/agent/xmltaxpayer.xsd">'
            . '<beallitasok><szamlaagentkulcs>' . htmlspecialchars($apiKey, ENT_XML1) . '</szamlaagentkulcs></beallitasok>'
            . '<torzsszam>' . $digits . '</torzsszam>'
            . '</xmltaxpayer>';

        try {
            $response = Http::timeout(15)
                ->attach('action-szamla_agent_taxpayer', $xml, 'taxpayer.xml', ['Content-Type' => 'text/xml'])
                ->post('https://www.szamlazz.hu/szamla/');
        } catch (\Throwable $e) {
            return response()->json([
                'taxpayers' => [],
                'error' => 'A szamlazz.hu lekérdezés sikertelen volt.',
            ]);
        }

        $body = (string) $response->body();
        $debugResponse = config('app.debug') ? mb_substr($body, 0, 4000) : null;

        $dom = new \DOMDocument();
        if (!@$dom->loadXML($body)) {
            return response()->json([
                'taxpayers' => [],
                'error' => 'Értelmezhetetlen válasz érkezett a szamlazz.hu-tól.',
                'debug_response' => $debugResponse,
            ]);
        }

        // Namespace-agnosztikus XPath — a NAV 2.0 (default api ns + ns2 data)
        // és 3.0 (ns2 api + ns3 base) válaszformátumot is kezeli.
        $xp = new \DOMXPath($dom);

        $funcCode = strtoupper(trim((string) $xp->evaluate('string(//*[local-name()="result"]/*[local-name()="funcCode"])')));
        if ($funcCode !== 'OK') {
            $message = trim((string) $xp->evaluate('string(//*[local-name()="result"]/*[local-name()="message"])'));
            if ($message === '') {
                // Régi szlavalasz-formátumú hiba (pl. hibás agent kulcs)
                $message = trim((string) $xp->evaluate('string(//*[local-name()="hibauzenet"])'));
            }
            return response()->json([
                'taxpayers' => [],
                'error' => $message !== '' ? $message : 'Hibás adószám lekérdezés.',
                'debug_response' => $debugResponse,
            ]);
        }

        $valid = strtolower(trim((string) $xp->evaluate('string(//*[local-name()="taxpayerValidity"])'))) === 'true';
        if (!$valid) {
            return response()->json([
                'taxpayers' => [],
                'invalid' => true,
                'debug_response' => $debugResponse,
            ]);
        }

        $dataNode = $xp->query('//*[local-name()="taxpayerData"]')->item(0);
        if (!$dataNode) {
            return response()->json(['taxpayers' => []]);
        }

        $name = trim((string) $xp->evaluate('string(*[local-name()="taxpayerName"])', $dataNode));

        $taxpayerId = trim((string) $xp->evaluate('string(*[local-name()="taxNumberDetail"]/*[local-name()="taxpayerId"])', $dataNode));
        if ($taxpayerId === '') {
            $taxpayerId = $digits;
        }
        $vatCode = trim((string) $xp->evaluate('string(*[local-name()="taxNumberDetail"]/*[local-name()="vatCode"])', $dataNode));
        $customerVatCode = trim((string) $xp->evaluate('string(*[local-name()="taxNumberDetail"]/*[local-name()="customerVatCode"])', $dataNode));
        if ($customerVatCode === '') {
            // NAV OSA 3.0: countyCode szerepel a harmadik szegmens helyett
            $customerVatCode = trim((string) $xp->evaluate('string(*[local-name()="taxNumberDetail"]/*[local-name()="countyCode"])', $dataNode));
        }

        $taxNumber = $taxpayerId;
        if ($vatCode !== '') {
            $taxNumber .= '-' . $vatCode;
        }
        if ($customerVatCode !== '') {
            $taxNumber .= '-' . $customerVatCode;
        }

        $addressTypeLabels = [
            'HQ' => 'Szekhely',
            'SITE' => 'Telephely',
            'BRANCH' => 'Fióktelep',
            'UNKNOWN' => '',
        ];

        $taxpayers = [];
        foreach ($xp->query('*[local-name()="taxpayerAddressList"]/*[local-name()="taxpayerAddressItem"]', $dataNode) ?: [] as $item) {
            $type = strtoupper(trim((string) $xp->evaluate('string(*[local-name()="taxpayerAddressType"])', $item)));
            $addr = $xp->query('*[local-name()="taxpayerAddress"]', $item)->item(0);

            $street = trim((string) $xp->evaluate('string(*[local-name()="streetName"])', $addr));
            $placeCategory = trim((string) $xp->evaluate('string(*[local-name()="publicPlaceCategory"])', $addr));
            $placeCategory = in_array(strtoupper($placeCategory), ['', 'N/A'], true) ? '' : mb_strtolower($placeCategory);
            $number = trim((string) $xp->evaluate('string(*[local-name()="number"])', $addr));

            $addressLine = trim(implode(' ', array_filter([$street, $placeCategory, $number])));
            $extraParts = array_filter([
                trim((string) $xp->evaluate('string(*[local-name()="building"])', $addr)),
                trim((string) $xp->evaluate('string(*[local-name()="staircase"])', $addr)),
                trim((string) $xp->evaluate('string(*[local-name()="floor"])', $addr)),
                trim((string) $xp->evaluate('string(*[local-name()="door"])', $addr)),
            ]);
            if ($extraParts) {
                $addressLine .= ' (' . implode(' ', $extraParts) . ')';
            }

            $country = trim((string) $xp->evaluate('string(*[local-name()="countryCode"])', $addr));

            $taxpayers[] = [
                'name' => $name,
                'tax_number' => $taxNumber,
                'address_type' => $type,
                'address_type_label' => $addressTypeLabels[$type] ?? $type,
                'country' => $country !== '' ? $country : 'HU',
                'zip_code' => trim((string) $xp->evaluate('string(*[local-name()="postalCode"])', $addr)),
                'city' => trim((string) $xp->evaluate('string(*[local-name()="city"])', $addr)),
                'address_line' => $addressLine,
            ];
        }

        if (empty($taxpayers)) {
            $taxpayers[] = [
                'name' => $name,
                'tax_number' => $taxNumber,
                'address_type' => '',
                'address_type_label' => '',
                'country' => 'HU',
                'zip_code' => '',
                'city' => '',
                'address_line' => '',
            ];
        }

        return response()->json(['taxpayers' => $taxpayers]);
    }
}
