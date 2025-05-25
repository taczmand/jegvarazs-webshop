<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxCategoryRequest;
use App\Models\TaxCategory;
use App\Services\Admin\TaxCategoryService;
use Yajra\DataTables\Facades\DataTables;

class TaxCategoryController extends Controller
{
    protected $tax_category_service;

    public function __construct(TaxCategoryService $service)
    {
        $this->tax_category_service = $service;
    }
    public function index()
    {
        return view('admin.settings.tax-categories');
    }

    public function data()
    {
        $taxes = TaxCategory::select(['id', 'tax_value', 'tax_name', 'tax_description', 'created_at']);

        return DataTables::of($taxes)
            ->addColumn('action', function ($tax) {
                return '
                    <button class="btn btn-sm btn-primary edit" data-id="'.$tax->id.'" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$tax->id.'" title="Törlés">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(TaxCategoryRequest $request)
    {
        $result = $this->tax_category_service->store($request->validated());

        if ($result['success']) {
            return response()->json(['message' => 'Mentés sikeres', 'tax' => $result['data']]);
        } else {
            return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
        }
    }

    public function update(TaxCategoryRequest $request, TaxCategory $tax)
    {
        $result = $this->tax_category_service->update($tax, $request->validated());

        if ($result['success']) {
            return response()->json(['message' => 'Mentés sikeres', 'tax' => $result['data']]);
        } else {
            return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
        }
    }

    public function destroy(TaxCategory $tax)
    {
        $result = $this->tax_category_service->delete($tax);

        if ($result['success']) {
            return response()->json(['message' => 'Sikeres törlés.']);
        }

        return response()->json([
            'error' => 'Nem sikerült törölni az adóosztályt.',
            'details' => $result['error']
        ], 500);
    }
}
