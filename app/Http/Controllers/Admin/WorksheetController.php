<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class WorksheetController extends Controller
{
    public function index()
    {
        return view('admin.business.worksheets');
    }

    public function create()
    {
        return view('admin.business.worksheet_create');
    }

    public function data()
    {
        // This method should return the data for the worksheets.
        // Implement the logic to fetch and return worksheet data.
        // For now, returning an empty response.
        return response()->json([]);
    }
}
