<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Searched;
use App\Models\WatchedProduct;
use Yajra\DataTables\Facades\DataTables;

class StatController extends Controller
{
    public function searchedProducts()
    {
        return view('admin.statistics.searches');
    }

    public function searchedProductsData()
    {
        $items = Searched::select(['id', 'search_term', 'number_of_hits', 'ip_address', 'created_at as created']);

        return DataTables::of($items)
            ->make(true);
    }

    public function watchedProducts()
    {
        return view('admin.statistics.viewed_products');
    }

    public function watchedProductsData()
    {
        $items = WatchedProduct::query()
            ->join('products', 'watched_products.product_id', '=', 'products.id')
            ->select([
                'watched_products.product_id',
                'products.title as product_title',
                \DB::raw('COUNT(*) as number_of_hits'),
            ])
            ->groupBy('watched_products.product_id', 'products.title');

        return DataTables::of($items)->make(true);
    }

    public function adminLogs()
    {
        return view('admin.statistics.admin_logs');
    }
    public function adminLogsData()
    {
        $items = \DB::table('user_actions')
            ->join('users', 'user_actions.user_id', '=', 'users.id')
            ->select([
                'user_actions.id',
                'users.name as user_name',
                'user_actions.action',
                'user_actions.model',
                'user_actions.data',
                'user_actions.created_at',
            ])
            ->orderBy('user_actions.created_at', 'desc');

        return \DataTables::of($items)
            ->filter(function ($query) {
                if ($userName = request('user_name')) {
                    $query->where('users.name', 'like', '%' . $userName . '%');
                }

                if ($model = request('model')) {
                    $query->where('user_actions.model', $model);
                }

                if ($action = request('action')) {
                    $query->where('user_actions.action', $action);
                }

                if ($data= request('data')) {
                    $query->where('user_actions.data', 'like', '%' .$data . '%');
                }
            })
            ->make(true);
    }

    public function purchasedProducts()
    {
        return view('admin.statistics.purchased_products');
    }
    public function purchasedProductsData()
    {
        $items = \DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select([
                'order_items.product_id',
                'products.title as product_title',
                \DB::raw('SUM(order_items.quantity) as total_quantity'),
                \DB::raw('SUM(order_items.gross_price * order_items.quantity) as total_price'),
            ])
            ->groupBy('order_items.product_id', 'products.title');

        return DataTables::of($items)->make(true);
    }


}
