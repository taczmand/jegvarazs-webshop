<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Contract;
use App\Models\Worksheet;
use Illuminate\Console\Command;

class SyncClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:sync {--dry-run : Do not write to database, only show stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ügyfelek szinkronizálása szerződésekből, munkalapokból és időpontfoglalásokból az ügyféltörzsbe.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $stats = [
            'sources' => [
                'contracts' => 0,
                'worksheets' => 0,
                'appointments' => 0,
            ],
            'clients' => [
                'processed' => 0,
                'skipped_no_email' => 0,
                'skipped_invalid_email' => 0,
                'created' => 0,
                'updated' => 0,
            ],
            'addresses' => [
                'created' => 0,
                'existing' => 0,
            ],
        ];

        $syncOne = function (array $payload) use (&$stats, $dryRun): void {
            $stats['clients']['processed']++;

            $email = $this->normalizeEmail($payload['email'] ?? null);
            if (!$email) {
                $stats['clients']['skipped_no_email']++;
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stats['clients']['skipped_invalid_email']++;
                return;
            }

            $clientData = [
                'name' => $this->normalizeString($payload['name'] ?? null),
                'mothers_name' => $this->normalizeString($payload['mothers_name'] ?? null),
                'place_of_birth' => $this->normalizeString($payload['place_of_birth'] ?? null),
                'date_of_birth' => $payload['date_of_birth'] ?? null,
                'id_number' => $this->normalizeString($payload['id_number'] ?? null),
                'phone' => $this->normalizeString($payload['phone'] ?? null),
            ];

            $clientData = array_filter($clientData, fn ($v) => $v !== null && $v !== '');

            $addressData = $this->normalizeAddress([
                'country' => $payload['country'] ?? null,
                'zip_code' => $payload['zip_code'] ?? null,
                'city' => $payload['city'] ?? null,
                'address_line' => $payload['address_line'] ?? null,
            ]);

            if ($dryRun) {
                $this->line("[DRY] {$email}");
                return;
            }

            $existingClient = Client::where('email', $email)->first();

            $client = Client::updateOrCreate(
                ['email' => $email],
                $clientData
            );

            if ($existingClient) {
                $stats['clients']['updated']++;
            } else {
                $stats['clients']['created']++;
            }

            $this->syncAddress($client, $addressData, $stats);
        };

        foreach (Contract::query()->select([
            'name',
            'mothers_name',
            'place_of_birth',
            'date_of_birth',
            'id_number',
            'email',
            'phone',
            'country',
            'zip_code',
            'city',
            'address_line',
        ])->cursor() as $contract) {
            $stats['sources']['contracts']++;
            $syncOne([
                'name' => $contract->name,
                'mothers_name' => $contract->mothers_name,
                'place_of_birth' => $contract->place_of_birth,
                'date_of_birth' => $contract->date_of_birth,
                'id_number' => $contract->id_number,
                'email' => $contract->email,
                'phone' => $contract->phone,
                'country' => $contract->country,
                'zip_code' => $contract->zip_code,
                'city' => $contract->city,
                'address_line' => $contract->address_line,
            ]);
        }

        foreach (Worksheet::query()->select(['name', 'email', 'phone', 'country', 'zip_code', 'city', 'address_line'])->cursor() as $worksheet) {
            $stats['sources']['worksheets']++;
            $syncOne([
                'name' => $worksheet->name,
                'email' => $worksheet->email,
                'phone' => $worksheet->phone,
                'country' => $worksheet->country,
                'zip_code' => $worksheet->zip_code,
                'city' => $worksheet->city,
                'address_line' => $worksheet->address_line,
            ]);
        }

        foreach (Appointment::query()->select(['name', 'email', 'phone', 'zip_code', 'city', 'address_line'])->cursor() as $appointment) {
            $stats['sources']['appointments']++;
            $syncOne([
                'name' => $appointment->name,
                'email' => $appointment->email,
                'phone' => $appointment->phone,
                'country' => 'HU',
                'zip_code' => $appointment->zip_code,
                'city' => $appointment->city,
                'address_line' => $appointment->address_line,
            ]);
        }

        $this->info('SyncClients kész.');
        $this->line(json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return 0;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = $this->normalizeString($email);
        return $email ? mb_strtolower($email) : null;
    }

    private function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        return $value !== '' ? $value : null;
    }

    private function normalizeAddress(array $address): array
    {
        $country = $this->normalizeString($address['country'] ?? null) ?: 'HU';

        return [
            'country' => $country,
            'zip_code' => $this->normalizeString($address['zip_code'] ?? null),
            'city' => $this->normalizeString($address['city'] ?? null),
            'address_line' => $this->normalizeString($address['address_line'] ?? null),
        ];
    }

    private function syncAddress(Client $client, array $addressData, array &$stats): void
    {
        $hasAnyAddressField = (bool) ($addressData['zip_code'] || $addressData['city'] || $addressData['address_line']);
        if (!$hasAnyAddressField) {
            return;
        }

        $query = ClientAddress::query()
            ->where('client_id', $client->id)
            ->where('country', $addressData['country'])
            ->where('zip_code', $addressData['zip_code'])
            ->where('city', $addressData['city'])
            ->where('address_line', $addressData['address_line']);

        $existing = $query->first();
        if ($existing) {
            $stats['addresses']['existing']++;
            return;
        }

        $hasDefault = ClientAddress::where('client_id', $client->id)->where('is_default', true)->exists();

        ClientAddress::create([
            'client_id' => $client->id,
            'country' => $addressData['country'],
            'zip_code' => $addressData['zip_code'],
            'city' => $addressData['city'],
            'address_line' => $addressData['address_line'],
            'comment' => null,
            'is_default' => !$hasDefault,
        ]);

        $stats['addresses']['created']++;
    }
}
