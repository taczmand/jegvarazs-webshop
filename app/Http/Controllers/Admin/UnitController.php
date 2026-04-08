<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Services\Admin\UnitService;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    protected $unit_service;

    public function __construct(UnitService $service)
    {
        $this->unit_service = $service;
    }

    public function index()
    {
        return view('admin.settings.units');
    }

    public function data()
    {
        $units = Unit::select(['id', 'name', 'abbreviation', 'active']);

        return DataTables::of($units)
            ->editColumn('active', function ($unit) {
                return $unit->active ? 'Aktív' : 'Inaktív';
            })
            ->addColumn('action', function ($unit) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-unit')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $unit->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-unit')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $unit->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(UnitRequest $request)
    {
        $result = $this->unit_service->store($request->validated());

        if ($result['success']) {
            return response()->json(['message' => 'Mentés sikeres', 'unit' => $result['data']]);
        }

        return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $result = $this->unit_service->update($unit, $request->validated());

        if ($result['success']) {
            return response()->json(['message' => 'Mentés sikeres', 'unit' => $result['data']]);
        }

        return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
    }

    public function destroy(Unit $unit)
    {
        $result = $this->unit_service->delete($unit);

        if ($result['success']) {
            return response()->json(['message' => 'Sikeres törlés.']);
        }

        return response()->json([
            'error' => 'Nem sikerült törölni a mértékegységet.',
            'details' => $result['error']
        ], 500);
    }
}
