<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.sales.orders');
    }
    public function data()
    {
        $orders = Order::with(['items', 'customer'])->select(['id', 'status', 'created_at', 'customer_id']);

        return datatables()
            ->of($orders)
            ->addColumn('total_amount', function ($order) {
                return $order->items->sum(function ($item) {
                    return $item->quantity * $item->product->gross_price;
                });
            })
            ->addColumn('items_count', function ($order) {
                return $order->items->count();
            })
            ->addColumn('customer_name', function ($order) {
                return $order->customer ? $order->customer->last_name ." ". $order->customer->first_name : 'N/A';
            })
            ->editColumn('created_at', function ($order) {
                // Formázás: YYYY-MM-DD HH:mm:ss
                return $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($product) {
                return '
                    <button class="btn btn-sm btn-primary edit" data-id="'.$product->id.'" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$product->id.'" title="Törlés">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        return Order::with(['items.product', 'customer'])->findOrFail($id);
    }

    public function history($id)
    {
        return OrderHistory::with(['customer', 'user'])
            ->where('order_id', $id)
            ->get();

    }
}
