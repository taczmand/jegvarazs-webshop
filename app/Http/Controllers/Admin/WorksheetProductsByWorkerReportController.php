<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorksheetProductsByWorkerReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-worksheet-products-by-worker-report')) {
            abort(403);
        }

        $currentYear = (int) ($request->query('year') ?: now()->year);
        $requestedMonth = $request->query('month');
        $month = is_numeric($requestedMonth) ? (int) $requestedMonth : 0;
        if ($month < 1 || $month > 12) {
            $month = 0;
        }
        $requestedType = $request->query('work_type');
        $workType = is_string($requestedType) ? trim($requestedType) : '';

        $yearsQuery = Worksheet::query()
            ->selectRaw('YEAR(installation_date) as year')
            ->distinct()
            ->whereIn('work_status', ['Kész', 'Lezárva']);

        if ($month > 0) {
            $yearsQuery->whereMonth('installation_date', $month);
        }

        if ($workType !== '') {
            $yearsQuery->where('work_type', $workType);
        }

        $yearsQuery->orderByDesc('year');

        if ($user->can('view-own-worksheets') && !$user->can('view-worksheets')) {
            $yearsQuery->whereHas('workers', fn ($q) => $q->where('users.id', $user->id));
        }

        $years = $yearsQuery->pluck('year')->map(fn ($y) => (int) $y)->values()->all();

        if (count($years) > 0 && !in_array($currentYear, $years, true)) {
            $currentYear = (int) $years[0];
        }

        return view('admin.statistics.worksheet_products_by_worker', [
            'years' => $years,
            'currentYear' => $currentYear,
            'currentWorkType' => $workType,
            'currentMonth' => $month,
        ]);
    }

    public function data(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-worksheet-products-by-worker-report')) {
            return response()->json(['message' => 'Nincs jogosultságod a jelentés megtekintéséhez.'], 403);
        }

        $year = (int) ($request->query('year') ?: now()->year);
        if ($year < 2000 || $year > 2100) {
            return response()->json(['message' => 'Érvénytelen év.'], 422);
        }

        $requestedMonth = $request->query('month');
        $month = is_numeric($requestedMonth) ? (int) $requestedMonth : 0;
        if ($month < 1 || $month > 12) {
            $month = 0;
        }

        $requestedType = $request->query('work_type');
        $workType = is_string($requestedType) ? trim($requestedType) : '';
        $allowedTypes = ['Karbantartás', 'Szerelés', 'Felmérés'];
        if ($workType !== '' && !in_array($workType, $allowedTypes, true)) {
            return response()->json(['message' => 'Érvénytelen munkalap típus.'], 422);
        }

        $query = DB::table('worksheets')
            ->join('worksheet_products', 'worksheets.id', '=', 'worksheet_products.worksheet_id')
            ->join('products', 'worksheet_products.product_id', '=', 'products.id')
            ->join('worksheet_workers', 'worksheets.id', '=', 'worksheet_workers.worksheet_id')
            ->leftJoin('users as worker', 'worksheet_workers.worker_id', '=', 'worker.id')
            ->whereYear('worksheets.installation_date', $year)
            ->when($month > 0, fn ($q) => $q->whereMonth('worksheets.installation_date', $month))
            ->when($workType !== '', fn ($q) => $q->where('worksheets.work_type', $workType), fn ($q) => $q->whereIn('worksheets.work_type', $allowedTypes))
            ->whereIn('worksheets.work_status', ['Kész', 'Lezárva'])
            ->select([
                DB::raw('COALESCE(worker.id, 0) as worker_id'),
                DB::raw('COALESCE(worker.name, "Ismeretlen") as worker_name'),
                DB::raw('SUM(CASE WHEN COALESCE(products.count_in_contract_products_report, 1) = 1 THEN COALESCE(worksheet_products.quantity, 0) ELSE 0 END) as qty'),
            ])
            ->groupBy(DB::raw('COALESCE(worker.id, 0)'), DB::raw('COALESCE(worker.name, "Ismeretlen")'))
            ->orderByDesc('qty');

        if ($user->can('view-own-worksheets') && !$user->can('view-worksheets')) {
            $query->where('worksheet_workers.worker_id', $user->id);
        }

        $rows = $query->get();

        $dataPoints = [];
        foreach ($rows as $r) {
            $dataPoints[] = [
                'label' => (string) $r->worker_name,
                'y' => (int) $r->qty,
            ];
        }

        return response()->json([
            'year' => $year,
            'work_type' => $workType,
            'month' => $month,
            'dataPoints' => $dataPoints,
        ]);
    }
}
