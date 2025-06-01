<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class StockStatusesController extends Controller
{
    public function index()
    {
        return view('admin.settings.stock-statuses');
    }
}
