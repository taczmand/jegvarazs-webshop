<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AttributeController extends Controller
{
    public function index()
    {
        return view('admin.products.attributes');
    }

    public function data()
    {
        $attributes = Attribute::select(['id', 'name', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($attributes)
            ->addColumn('action', function ($attribute) {
                return '
                    <button class="btn btn-sm btn-primary edit" data-id="'.$attribute->id.'" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$attribute->id.'" title="Törlés">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(AttributeRequest $request)
    {
        try {
            $attribute = Attribute::create([
                'name' => $request['name']
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'attribute' => $attribute,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Egyedi tulajdonság mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(AttributeRequest $request)
    {
        try {
            $attribute = Attribute::findOrFail($request['id']);

            $attribute->update([
                'name' => $request['name']
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'attribute' => $attribute,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Egyedi tulajdonság mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        try {
            $attribute = Attribute::findOrFail($request->id);
            $attribute->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Egyedi tulajdonság törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }

}
