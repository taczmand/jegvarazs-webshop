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

        $selectedFormName = $request->query('form_name');
        $selectedFormName = is_string($selectedFormName) ? trim($selectedFormName) : null;
        $selectedFormName = $selectedFormName !== '' ? $selectedFormName : null;

        $formNames = Lead::query()
            ->whereNotNull('form_name')
            ->where('form_name', '<>', '')
            ->select('form_name')
            ->distinct()
            ->orderBy('form_name')
            ->pluck('form_name')
            ->values()
            ->all();

        return view('admin.statistics.lead_conversion', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'formNames' => $formNames,
            'selectedFormName' => $selectedFormName,
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
            'form_name' => ['nullable', 'string'],
        ]);

        $from = Carbon::parse($request->query('from'))->startOfDay();
        $to = Carbon::parse($request->query('to'))->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $formName = $request->query('form_name');
        $formName = is_string($formName) ? trim($formName) : null;
        $formName = $formName !== '' ? $formName : null;

        $leadsQuery = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->select(['id', 'email', 'phone', 'status', 'created_at']);

        if ($formName) {
            $leadsQuery->where('form_name', $formName);
        }

        $leads = $leadsQuery->get();

        $leadCount = $leads->count();

        if ($leadCount === 0) {
            return response()->json([
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'form_name' => $formName,
                'counts' => [
                    'leads' => 0,
                    'survey' => 0,
                    'contract' => 0,
                    'contract_products_qty' => 0,
                ],
            ]);
        }

        $leadIds = $leads->pluck('id')->all();

        $contractsQuery = Contract::query()
            ->select(['id', 'lead_id', 'created_at'])
            ->whereNotNull('lead_id')
            ->whereIn('lead_id', $leadIds)
            ->where('created_at', '>=', $from);

        if ($user->can('view-own-contracts') && !$user->can('view-contracts')) {
            $contractsQuery->where('created_by', $user->id);
        }

        $contracts = $contractsQuery->get();

        $contractsByLeadId = [];
        foreach ($contracts as $c) {
            $lid = (int) ($c->lead_id ?? 0);
            if ($lid > 0) {
                $contractsByLeadId[$lid][] = $c;
            }
        }

        $assignedLeadIds = [];
        $matchedContractIds = [];
        foreach ($leads as $lead) {
            $lid = (int) ($lead->id ?? 0);
            if ($lid <= 0) {
                continue;
            }

            if (!isset($contractsByLeadId[$lid])) {
                continue;
            }

            $leadCreatedAt = $lead->created_at ? Carbon::parse($lead->created_at) : null;
            $has = false;

            foreach ($contractsByLeadId[$lid] as $contract) {
                if (!$leadCreatedAt) {
                    $has = true;
                    $matchedContractIds[] = (int) $contract->id;
                    continue;
                }

                if ($contract->created_at && Carbon::parse($contract->created_at)->greaterThanOrEqualTo($leadCreatedAt)) {
                    $has = true;
                    $matchedContractIds[] = (int) $contract->id;
                }
            }

            if ($has) {
                $assignedLeadIds[] = $lid;
            }
        }

        $assignedLeadIds = array_values(array_unique($assignedLeadIds));
        $matchedContractIds = array_values(array_unique(array_filter($matchedContractIds, fn ($id) => (int) $id > 0)));

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

        $contractCount = count($assignedLeadIds);

        $contractProductsQty = 0;
        if (count($matchedContractIds) > 0) {
            $contractProductsQty = (int) DB::table('contract_products')
                ->whereIn('contract_id', $matchedContractIds)
                ->sum(DB::raw('COALESCE(product_qty, 0)'));
        }

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'form_name' => $formName,
            'counts' => [
                'leads' => $leadCount,
                'survey' => $surveyCount,
                'contract' => $contractCount,
                'contract_products_qty' => $contractProductsQty,
            ],
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '';
    }
}
