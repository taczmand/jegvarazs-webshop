<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class OrderStatusesController extends Controller
{
    public function index()
    {
        return view('admin.settings.order-statuses');
    }
}
