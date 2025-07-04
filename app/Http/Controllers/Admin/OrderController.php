<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.sales.orders');
    }
    public function data()
    {
        $orders = Order::select([
            'orders.id',
            'orders.status',
            'orders.created_at',
            'orders.customer_id',
            'customers.last_name',
            'customers.first_name',
        ])->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->with('items', 'customer');

        return datatables()
            ->of($orders)
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('orders.status', $order);
            })
            ->orderColumn('customer_name', function ($query, $order) {
                $query->orderBy('customers.last_name', $order)
                    ->orderBy('customers.first_name', $order);
            })
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('orders.id', '=', $keyword);
                }
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('customers.last_name', 'like', "%{$keyword}%")
                        ->orWhere('customers.first_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('orders.status', 'like', "%{$keyword}%");
                }
            })
            ->addColumn('status', function ($order) {
                $translations = [
                    'pending'   => 'Függőben',
                    'completed' => 'Teljesítve',
                    'cancelled' => 'Törölve',
                    'processing'=> 'Feldolgozás alatt',
                ];
                return $translations[$order->status] ?? ucfirst($order->status);
            })
            ->addColumn('total_amount', function ($order) {
                return $order->items->sum(function ($item) {
                    return $item->quantity * $item->product->gross_price;
                });
            })
            ->addColumn('items_count', function ($order) {
                return $order->items->count();
            })
            ->addColumn('customer_name', function ($order) {
                return $order->last_name && $order->first_name
                    ? $order->last_name . ' ' . $order->first_name
                    : ($order->customer ? $order->customer->last_name . ' ' . $order->customer->first_name : 'N/A');
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($order) {
                return '
                <button class="btn btn-sm btn-primary edit" data-id="' . $order->id . '" title="Szerkesztés">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger delete" data-id="' . $order->id . '" title="Törlés">
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

    public function items($id)
    {
        return OrderItem::with(['product'])
            ->where('order_id', $id)
            ->get();
    }

    public function update($id)
    {
        $order = Order::findOrFail($id);
        $order->status = request('status');
        $order->comment = request('order_comment', $order->comment);
        $order->contact_first_name = request('contact_first_name', $order->contact_first_name);
        $order->contact_last_name = request('contact_last_name', $order->contact_last_name);
        $order->contact_email = request('contact_email', $order->contact_email);
        $order->contact_phone = request('contact_phone', $order->contact_phone);
        $order->billing_name = request('billing_name', $order->billing_name);
        $order->billing_country = request('billing_country', $order->billing_country);
        $order->billing_postal_code = request('billing_postal_code', $order->billing_postal_code);
        $order->billing_city = request('billing_city', $order->billing_city);
        $order->billing_address_line = request('billing_address_line', $order->billing_address_line);
        $order->billing_tax_number = request('billing_tax_number', $order->billing_tax_number);
        $order->shipping_name = request('shipping_name', $order->shipping_name);
        $order->shipping_country = request('shipping_country', $order->shipping_country);
        $order->shipping_postal_code = request('shipping_postal_code', $order->shipping_postal_code);
        $order->shipping_city = request('shipping_city', $order->shipping_city);
        $order->shipping_address_line = request('shipping_address_line', $order->shipping_address_line);


        if ($order->isDirty()) {

            $order->save();

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => auth('admin')->id(),
                'action' => 'order_updated',
                'data' => json_encode([
                    'order' => $order->toArray()
                ]),
            ]);
            return response()->json([
                'message' => 'Rendelés sikeresen frissítve.',
                'order' => $order,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Nincs változás a rendelésben.',
            ], 200);
        }


    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->items()->delete(); // Töröljük a rendelés tételeit
        $order->delete(); // Töröljük magát a rendelést

        return response()->json([
            'message' => 'Rendelés sikeresen törölve.',
        ], 200);
    }
}
