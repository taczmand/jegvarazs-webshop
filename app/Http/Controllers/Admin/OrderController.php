<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\OrderService;

class OrderController extends Controller
{
    protected $order_service;

    public function __construct(OrderService $service)
    {
        $this->order_service = $service;
    }

    public function index()
    {
        return view('admin.sales.orders');
    }
}
