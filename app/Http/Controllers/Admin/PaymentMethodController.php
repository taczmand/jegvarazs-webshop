<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return view('admin.settings.payment-methods');
    }
}
