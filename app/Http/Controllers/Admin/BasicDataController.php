<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BasicDataRequest;
use App\Models\BasicData;
use App\Models\UserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;

class BasicDataController extends Controller
{

    public function index()
    {
        return view('admin.settings.general');
    }

    public function data()
    {
        $data_rows = BasicData::select(['id', 'key', 'value']);
        return DataTables::of($data_rows)
            ->addColumn('action', function ($data) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-settings')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $data->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function update(BasicDataRequest $request)
    {
        $data = $request->validated();

        $basicData = BasicData::find($data['id']);


        try {
            $basicData->update($data);
            return response()->json(['message' => 'Mentés sikeres', 'data' => $basicData]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

    public function getNewRecords(): JsonResponse
    {
        $tables = [
            'offers',
            'customers',
            'orders',
            'appointments',
            'contracts',
            'leads',
        ];

        $queries = [];

        foreach ($tables as $table) {
            $queries[] = \DB::table($table)
                ->select(
                    \DB::raw("'{$table}' as model"),
                    \DB::raw('COUNT(*) as count'),
                    \DB::raw('MAX(created_at) as latest')
                )
                ->whereNull('viewed_by');
        }

        // UNION ALL az összes lekérdezés között
        $unionQuery = array_shift($queries);
        foreach ($queries as $query) {
            $unionQuery = $unionQuery->unionAll($query);
        }

        $results = $unionQuery->orderByDesc('latest')->get()->map(function($item) {
            $item->latest = $item->latest ? \Carbon\Carbon::parse($item->latest)->format('Y-m-d H:i:s') : null;
            return $item;
        });

        return response()->json($results);
    }


    public function markAsViewed(Request $request)
    {
        $data = $request->validate([
            'model' => 'required|string',
            'id'    => 'required|integer',
        ]);

        try {
            $modelClass = '\\App\\Models\\' . ucfirst($data['model']);

            if (!class_exists($modelClass)) {
                return response()->json(['message' => 'Érvénytelen modell típus'], 400);
            }

            $record = $modelClass::findOrFail($data['id']);

            $user = auth('admin')->user();

            $record->update([
                'viewed_by' => $user ? $user->name : null,
                'viewed_at' => now(),
            ]);

            return response()->json(['message' => 'A rekord megjelölve mint megtekintett']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hiba történt: ' . $e->getMessage()
            ], 500);
        }
    }


}
