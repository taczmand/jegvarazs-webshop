<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BasicDataRequest;
use App\Models\BasicData;
use Yajra\DataTables\Facades\DataTables;

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
                return '
                    <button class="btn btn-sm btn-primary edit" data-id="'.$data->id.'" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>
                ';
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


}
