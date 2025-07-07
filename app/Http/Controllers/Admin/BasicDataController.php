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
        $newRecords = UserAction::query()
            ->select('model', DB::raw('count(*) as count'), DB::raw('max(created_at) as latest'))
            ->where('action', 'created')
            ->whereIn('model', [
                'orders',
                'coupons',
                'customers',
                'products',
                'categories',
                'attributes',
                'tags',
                'brands',
                'appointments',
                'offers',
                'contracts',
                'worksheets'
            ])
            ->whereNull('viewed_by')
            ->groupBy('model')
            ->orderByDesc('latest')
            ->get();

        return response()->json($newRecords);
    }

    public function markAsViewed(Request $request)
    {
        $data = $request->validate([
            'model' => 'required|string',
            'id' => 'required|integer',
        ]);

        try {
            UserAction::where('model', $data['model'])
                ->where('model_id', $data['id'])
                ->where('action', 'created')
                ->update([
                    'viewed_by' => auth('admin')->id(),
                    'viewed_at' => now(),
                ]);

            return response()->json(['message' => 'A rekord megjelölve mint megtekintett']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

}
