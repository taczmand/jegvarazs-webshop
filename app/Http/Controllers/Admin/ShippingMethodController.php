<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ShippingMethodController extends Controller
{
    public function index()
    {
        return view('admin.settings.shipping-methods');
    }
}
