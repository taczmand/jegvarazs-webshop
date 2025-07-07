<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        return view('admin.dashboard', [
            'orderCount' => Order::count(),
            'productCount' => Product::count(),
            'customerCount' => Customer::count(),
            'revenue' => OrderItem::sum('gross_price')
        ]);
    }
}
