<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index()
    {
        abort_unless(auth('admin')->user()?->can('view-warehouses'), 403);
        return view('admin.warehouse.warehouses');
    }

    public function data()
    {
        abort_unless(auth('admin')->user()?->can('view-warehouses'), 403);
        $warehouses = Warehouse::select([
            'id',
            'name',
            'code',
            'country',
            'zip_code',
            'city',
            'address_line',
            'is_active',
            'note',
            'created_at as created',
            'updated_at as updated',
        ]);

        return DataTables::of($warehouses)
            ->addColumn('action', function ($warehouse) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-warehouse')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $warehouse->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-warehouse')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $warehouse->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        abort_unless(auth('admin')->user()?->can('create-warehouse'), 403);
        $validated = $this->validateWarehouse($request);

        try {
            $warehouse = Warehouse::create($validated);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'warehouse' => $warehouse,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth('admin')->user()?->can('edit-warehouse'), 403);
        $validated = $this->validateWarehouse($request, $id);

        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->update($validated);

            return response()->json([
                'message' => 'Sikeres frissítés!',
                'warehouse' => $warehouse,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        abort_unless(auth('admin')->user()?->can('delete-warehouse'), 403);
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->delete();

            return response()->json(['message' => 'Sikeres törlés!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

    private function validateWarehouse(Request $request, ?int $warehouseId = null): array
    {
        $codeRule = 'nullable|string|max:50|unique:warehouses,code';
        if ($warehouseId) {
            $codeRule .= ',' . $warehouseId;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => $codeRule,
            'country' => ['required', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);
    }
}
