<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractProductsReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('view-contracts') && !$user->can('view-own-contracts'))) {
            abort(403);
        }

        $currentYear = (int) ($request->query('year') ?: now()->year);

        $yearsQuery = Contract::query()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year');

        if ($user->can('view-own-contracts') && !$user->can('view-contracts')) {
            $yearsQuery->where('created_by', $user->id);
        }

        $years = $yearsQuery->pluck('year')->map(fn ($y) => (int) $y)->values()->all();

        if (count($years) > 0 && !in_array($currentYear, $years, true)) {
            $currentYear = (int) $years[0];
        }

        return view('admin.statistics.contract_products', [
            'years' => $years,
            'currentYear' => $currentYear,
        ]);
    }

    public function data(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('view-contracts') && !$user->can('view-own-contracts'))) {
            return response()->json(['message' => 'Nincs jogosultságod a jelentés megtekintéséhez.'], 403);
        }

        $year = (int) ($request->query('year') ?: now()->year);
        if ($year < 2000 || $year > 2100) {
            return response()->json(['message' => 'Érvénytelen év.'], 422);
        }

        $query = DB::table('contracts')
            ->join('contract_products', 'contracts.id', '=', 'contract_products.contract_id')
            ->leftJoin('users', 'contracts.created_by', '=', 'users.id')
            ->whereYear('contracts.created_at', $year)
            ->select([
                DB::raw('MONTH(contracts.created_at) as month'),
                DB::raw('COALESCE(users.id, 0) as user_id'),
                DB::raw('COALESCE(users.name, "Ismeretlen") as user_name'),
                DB::raw('SUM(COALESCE(contract_products.product_qty, 0)) as qty'),
            ])
            ->groupBy(DB::raw('MONTH(contracts.created_at)'), DB::raw('COALESCE(users.id, 0)'), DB::raw('COALESCE(users.name, "Ismeretlen")'))
            ->orderBy('month');

        if ($user->can('view-own-contracts') && !$user->can('view-contracts')) {
            $query->where('contracts.created_by', $user->id);
        }

        $rows = $query->get();

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Máj',
            6 => 'Jún',
            7 => 'Júl',
            8 => 'Aug',
            9 => 'Szept',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $users = $rows->map(fn ($r) => ['id' => (int) $r->user_id, 'name' => (string) $r->user_name])
            ->unique('id')
            ->values();

        $matrix = [];
        foreach ($users as $u) {
            $matrix[$u['id']] = array_fill(1, 12, 0);
        }

        foreach ($rows as $r) {
            $m = (int) $r->month;
            $uid = (int) $r->user_id;
            if (!isset($matrix[$uid])) {
                $matrix[$uid] = array_fill(1, 12, 0);
            }
            if ($m >= 1 && $m <= 12) {
                $matrix[$uid][$m] = (int) $r->qty;
            }
        }

        $series = $users->map(function ($u) use ($matrix, $months) {
            $uid = (int) $u['id'];
            $points = [];
            for ($m = 1; $m <= 12; $m++) {
                $points[] = [
                    'label' => $months[$m],
                    'y' => (int) ($matrix[$uid][$m] ?? 0),
                ];
            }

            return [
                'name' => $u['name'],
                'dataPoints' => $points,
            ];
        })->values()->all();

        return response()->json([
            'year' => $year,
            'series' => $series,
        ]);
    }
}
