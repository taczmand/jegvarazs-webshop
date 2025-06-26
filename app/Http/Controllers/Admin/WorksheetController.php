<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worksheet;
use Yajra\DataTables\Facades\DataTables;

class WorksheetController extends Controller
{
    public function index()
    {
        return view('admin.business.worksheets');
    }


    public function data()
    {
        // TODO módosítani és befejezni
        $contracts = Worksheet::select([
            'contracts.id',
            'contracts.name',
            'contracts.country',
            'contracts.zip_code',
            'contracts.city',
            'contracts.address_line',
            'contracts.installation_date',
            'contracts.created_at as created',
            'users.name as creator_name'])
            ->leftJoin('users', 'contracts.created_by', '=', 'users.id');

        return DataTables::of($contracts)
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('contracts.id', '=', $keyword);
                }
            })
            ->addColumn('creator_name', function ($contract) {
                return $contract->creator_name ?? 'Ismeretlen';
            })
            ->addColumn('action', function ($contract) {
                return '
                    <button class="btn btn-sm btn-primary view" data-id="'.$contract->id.'" title="Megtekintés">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$contract->id.'" title="Törlés">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
