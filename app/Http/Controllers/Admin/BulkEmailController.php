<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminBulkEmail;
use App\Models\Client;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BulkEmailController extends Controller
{
    private const RECIPIENTS_PREVIEW_LIMIT = 2000;

    public function index()
    {
        return view('admin.business.bulk_email');
    }

    public function recipients(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('edit-client') && !$user->can('edit-customer'))) {
            return response()->json(['message' => 'Nincs jogosultságod a címzettek megtekintéséhez.'], 403);
        }

        $validated = $request->validate([
            'recipient_group' => 'required|string|in:all,clients,customers,custom',
            'client_ids' => 'nullable|array|max:2000',
            'client_ids.*' => 'integer|exists:clients,id',
            'customer_ids' => 'nullable|array|max:2000',
            'customer_ids.*' => 'integer|exists:customers,id',
            'manual_emails' => 'nullable|array|max:2000',
            'manual_emails.*' => 'nullable|string|max:255',
            'excluded_emails' => 'nullable|array|max:2000',
            'excluded_emails.*' => 'nullable|string|max:255',
        ]);

        $excluded = $this->normalizeEmailList($validated['excluded_emails'] ?? []);

        $result = $this->resolveRecipientEmails(
            recipientGroup: $validated['recipient_group'],
            clientIds: $validated['client_ids'] ?? [],
            customerIds: $validated['customer_ids'] ?? [],
            manualEmails: $validated['manual_emails'] ?? [],
            excludedEmails: $excluded,
            limit: self::RECIPIENTS_PREVIEW_LIMIT
        );

        return response()->json($result);
    }

    public function send(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('edit-client') && !$user->can('edit-customer'))) {
            return response()->json(['message' => 'Nincs jogosultságod e-mailt küldeni.'], 403);
        }

        $validated = $request->validate([
            'recipient_group' => 'required|string|in:all,clients,customers,custom',
            'client_ids' => 'nullable|array|max:2000',
            'client_ids.*' => 'integer|exists:clients,id',
            'customer_ids' => 'nullable|array|max:2000',
            'customer_ids.*' => 'integer|exists:customers,id',
            'manual_emails' => 'nullable|array|max:2000',
            'manual_emails.*' => 'nullable|string|max:255',
            'excluded_emails' => 'nullable|array|max:2000',
            'excluded_emails.*' => 'nullable|string|max:255',
            'subject' => 'required|string|max:180',
            'html' => 'required|string|max:200000',
        ]);

        $excluded = $this->normalizeEmailList($validated['excluded_emails'] ?? []);
        $emails = $this->resolveRecipientEmailsForSend(
            recipientGroup: $validated['recipient_group'],
            clientIds: $validated['client_ids'] ?? [],
            customerIds: $validated['customer_ids'] ?? [],
            manualEmails: $validated['manual_emails'] ?? [],
            excludedEmails: $excluded
        );

        if ($emails->count() === 0) {
            return response()->json(['message' => 'Nincs érvényes e-mail cím a címzettek között.'], 422);
        }

        $fromAddress = config('mail.from.address') ?: (env('MAIL_FROM_ADDRESS') ?: null);
        if (!$fromAddress) {
            return response()->json(['message' => 'Nincs beállítva feladó e-mail cím (MAIL_FROM_ADDRESS).'], 500);
        }

        $sent = 0;
        $chunks = $emails->chunk(200);

        try {
            foreach ($chunks as $chunk) {
                Mail::to($fromAddress)->bcc($chunk->all())->send(new AdminBulkEmail($validated['subject'], $validated['html']));
                $sent += $chunk->count();
            }

            return response()->json([
                'message' => 'E-mail elküldve.',
                'count' => $sent,
            ]);
        } catch (\Throwable $e) {
            Log::error('Bulk email send failed', [
                'error' => $e->getMessage(),
                'count' => $emails->count(),
                'user_id' => $user?->id,
            ]);

            return response()->json([
                'message' => 'Hiba történt az e-mail küldése során.',
            ], 500);
        }
    }

    private function normalizeEmailList(array $emails): array
    {
        return collect($emails)
            ->map(fn($e) => mb_strtolower(trim((string) $e)))
            ->filter(fn($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    private function resolveRecipientEmailsForSend(
        string $recipientGroup,
        array $clientIds,
        array $customerIds,
        array $manualEmails,
        array $excludedEmails
    ): Collection {
        $emails = $this->resolveBaseEmails(
            recipientGroup: $recipientGroup,
            clientIds: $clientIds,
            customerIds: $customerIds,
            manualEmails: $manualEmails
        );

        if (!empty($excludedEmails)) {
            $excluded = collect($excludedEmails);
            $emails = $emails->reject(fn($e) => $excluded->contains($e))->values();
        }

        return $emails;
    }

    private function resolveRecipientEmails(
        string $recipientGroup,
        array $clientIds,
        array $customerIds,
        array $manualEmails,
        array $excludedEmails,
        int $limit
    ): array {
        $emails = $this->resolveBaseEmails(
            recipientGroup: $recipientGroup,
            clientIds: $clientIds,
            customerIds: $customerIds,
            manualEmails: $manualEmails
        );

        if (!empty($excludedEmails)) {
            $excluded = collect($excludedEmails);
            $emails = $emails->reject(fn($e) => $excluded->contains($e))->values();
        }

        $total = $emails->count();
        $truncated = $total > $limit;

        return [
            'emails' => $emails->take($limit)->values()->all(),
            'total' => $total,
            'shown' => min($total, $limit),
            'truncated' => $truncated,
        ];
    }

    private function resolveBaseEmails(
        string $recipientGroup,
        array $clientIds,
        array $customerIds,
        array $manualEmails
    ): Collection {
        $emails = collect();

        if ($recipientGroup === 'all' || $recipientGroup === 'clients') {
            $clientQuery = Client::query()->whereNotNull('email');
            if ($recipientGroup === 'clients' && !empty($clientIds)) {
                $clientQuery->whereIn('id', $clientIds);
            }
            $emails = $emails->merge($clientQuery->pluck('email'));
        }

        if ($recipientGroup === 'all' || $recipientGroup === 'customers') {
            $customerQuery = Customer::query()->whereNotNull('email');
            if ($recipientGroup === 'customers' && !empty($customerIds)) {
                $customerQuery->whereIn('id', $customerIds);
            }
            $emails = $emails->merge($customerQuery->pluck('email'));
        }

        foreach ((array) $manualEmails as $e) {
            $emails->push($e);
        }

        return $emails
            ->map(fn($e) => mb_strtolower(trim((string) $e)))
            ->filter(fn($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();
    }
}
