<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CustomerService;

class CustomerController extends Controller
{
    protected $customer_service;

    public function __construct(CustomerService $service)
    {
        $this->customer_service = $service;
    }

    public function index()
    {
        return view('admin.sales.customers');
    }
}
