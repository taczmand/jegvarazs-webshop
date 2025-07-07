<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function index()
    {
        return view('admin.products.brands');
    }

    public function data()
    {
        $brands = Brand::select(['id', 'title', 'status', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($brands)
            ->addColumn('status', function ($row) {
                $translations = [
                    'active' => 'Aktív',
                    'inactive' => 'Inaktív'
                ];

                return $translations[$row->status] ?? ucfirst($row->status);
            })
            ->addColumn('action', function ($brand) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-brand')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $brand->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-brand')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $brand->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(BrandRequest $request)
    {
        try {
            $brand = Brand::create([
                'title' => $request['title'],
                'slug' => Str::slug($request['title'])
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'brand' => $brand,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Gyártó mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(BrandRequest $request)
    {
        try {
            $brand = Brand::findOrFail($request['id']);

            $brand->update([
                'title' => $request['title'],
                'slug' => Str::slug($request['title']),
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'brand' => $brand,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Gyártó mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        try {
            $brand = Brand::findOrFail($request->id);
            $brand->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Gyártó törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
