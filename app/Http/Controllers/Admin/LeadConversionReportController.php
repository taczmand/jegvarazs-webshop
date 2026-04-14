<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadConversionReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-leads') || (!$user->can('view-contracts') && !$user->can('view-own-contracts'))) {
            abort(403);
        }

        $to = $request->query('to') ? Carbon::parse($request->query('to')) : now();
        $from = $request->query('from') ? Carbon::parse($request->query('from')) : (clone $to)->subDays(29);

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return view('admin.statistics.lead_conversion', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function data(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-leads') || (!$user->can('view-contracts') && !$user->can('view-own-contracts'))) {
            return response()->json(['message' => 'Nincs jogosultságod a jelentés megtekintéséhez.'], 403);
        }

        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $from = Carbon::parse($request->query('from'))->startOfDay();
        $to = Carbon::parse($request->query('to'))->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $leads = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->select(['id', 'email', 'phone', 'status', 'created_at'])
            ->get();

        $leadCount = $leads->count();

        if ($leadCount === 0) {
            return response()->json([
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'counts' => [
                    'leads' => 0,
                    'survey' => 0,
                    'contract' => 0,
                ],
            ]);
        }

        $leadIds = $leads->pluck('id')->all();

        $surveyFromLogs = DB::table('user_actions')
            ->where('model', 'leads')
            ->whereIn('model_id', $leadIds)
            ->where('action', 'updated')
            ->where('data->new->status', 'Felmérés')
            ->distinct()
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $surveyCurrent = $leads
            ->filter(fn ($l) => (string) $l->status === 'Felmérés')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $surveyLeadIds = collect(array_merge($surveyFromLogs, $surveyCurrent))
            ->unique()
            ->values()
            ->all();

        $surveyCount = count($surveyLeadIds);

        $emails = $leads
            ->pluck('email')
            ->filter(fn ($e) => is_string($e) && trim($e) !== '')
            ->map(fn ($e) => mb_strtolower(trim((string) $e)))
            ->unique()
            ->values()
            ->all();

        $phones = $leads
            ->pluck('phone')
            ->filter(fn ($p) => is_string($p) && trim($p) !== '')
            ->map(fn ($p) => $this->normalizePhone((string) $p))
            ->filter(fn ($p) => $p !== '')
            ->unique()
            ->values()
            ->all();

        $contractsQuery = Contract::query()
            ->select(['id', 'email', 'phone', 'created_at'])
            ->where('created_at', '>=', $from);

        if ($user->can('view-own-contracts') && !$user->can('view-contracts')) {
            $contractsQuery->where('created_by', $user->id);
        }

        $contractsQuery->where(function ($q) use ($emails, $phones) {
            if (count($emails) > 0) {
                $q->orWhereIn(DB::raw('LOWER(email)'), $emails);
            }

            if (count($phones) > 0) {
                $q->orWhereNotNull('phone');
            }
        });

        $contracts = $contractsQuery->get();

        $contractsByEmail = [];
        foreach ($contracts as $c) {
            if (is_string($c->email) && trim($c->email) !== '') {
                $key = mb_strtolower(trim($c->email));
                $contractsByEmail[$key][] = $c;
            }
        }

        $contractsByPhone = [];
        foreach ($contracts as $c) {
            if (is_string($c->phone) && trim($c->phone) !== '') {
                $key = $this->normalizePhone((string) $c->phone);
                if ($key !== '') {
                    $contractsByPhone[$key][] = $c;
                }
            }
        }

        $contractLeadIds = [];
        foreach ($leads as $lead) {
            $leadCreatedAt = $lead->created_at ? Carbon::parse($lead->created_at) : null;

            $emailKey = (is_string($lead->email) && trim($lead->email) !== '')
                ? mb_strtolower(trim((string) $lead->email))
                : null;

            $phoneKey = (is_string($lead->phone) && trim($lead->phone) !== '')
                ? $this->normalizePhone((string) $lead->phone)
                : null;

            $candidates = [];
            if ($emailKey && isset($contractsByEmail[$emailKey])) {
                $candidates = array_merge($candidates, $contractsByEmail[$emailKey]);
            }
            if ($phoneKey && $phoneKey !== '' && isset($contractsByPhone[$phoneKey])) {
                $candidates = array_merge($candidates, $contractsByPhone[$phoneKey]);
            }

            $has = false;
            foreach ($candidates as $contract) {
                if (!$leadCreatedAt) {
                    $has = true;
                    break;
                }
                if ($contract->created_at && Carbon::parse($contract->created_at)->greaterThanOrEqualTo($leadCreatedAt)) {
                    $has = true;
                    break;
                }
            }

            if ($has) {
                $contractLeadIds[] = (int) $lead->id;
            }
        }

        $contractCount = count(array_values(array_unique($contractLeadIds)));

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'counts' => [
                'leads' => $leadCount,
                'survey' => $surveyCount,
                'contract' => $contractCount,
            ],
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '';
    }
}
