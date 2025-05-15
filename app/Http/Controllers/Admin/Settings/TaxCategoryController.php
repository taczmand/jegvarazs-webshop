<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\TaxCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaxCategoryController extends Controller
{
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
                    <button class="btn btn-sm btn-primary edit" data-id="'.$tax->id.'">Szerkesztés</button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$tax->id.'">Törlés</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
