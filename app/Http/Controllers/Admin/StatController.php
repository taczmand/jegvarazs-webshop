<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Searched;
use App\Models\WatchedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function contacts()
    {
        return view('admin.statistics.contacts');
    }
    public function contactsData(Request $request)
    {
        // 1. Adatok előkészítése
        $customers = DB::table('customers')
            ->select('first_name', 'last_name', 'email', 'phone', 'is_partner')
            ->get()
            ->map(fn($c) => [
                'name' => trim($c->last_name . ' ' . $c->first_name),
                'email' => $c->email,
                'phone' => $c->phone,
                'types' => $c->is_partner ? 'Szerelő partner' : 'Webshop vásárló',
            ]);

        $contracts = DB::table('contracts')
            ->select('name', 'email', 'phone')
            ->get()
            ->map(fn($k) => [
                'name' => $k->name,
                'email' => $k->email,
                'phone' => $k->phone,
                'types' => 'Szerződés',
            ]);

        $appointments = DB::table('appointments')
            ->select('name', 'email', 'phone')
            ->get()
            ->map(fn($a) => [
                'name' => $a->name,
                'email' => $a->email,
                'phone' => $a->phone,
                'types' => 'Időpontfoglalás',
            ]);

        $worksheets = DB::table('worksheets')
            ->select('name', 'email', 'phone')
            ->get()
            ->map(fn($w) => [
                'name' => $w->name,
                'email' => $w->email,
                'phone' => $w->phone,
                'types' => 'Munkalap',
            ]);

        // 2. Összefésülés és azonos email-ek kezelése
        $all = $customers->merge($contracts)->merge($appointments)->merge($worksheets);

        $merged = $all->groupBy(fn($item) => strtolower(trim($item['email'] ?? '')))
            ->map(fn($group) => [
                'name'  => $group->first()['name'],
                'email' => $group->first()['email'],
                'phone' => $group->first()['phone'],
                'types' => $group->pluck('types')->unique()->implode(', '), // vesszővel elválasztva
            ])
            ->values();

        // 3. DataTables szerveroldali filter a columns[i][search][value] alapján
        return DataTables::of($merged)
            ->filter(function ($query) use ($request) {
                $columns = $request->input('columns', []);

                $query->collection = $query->collection->filter(function ($item) use ($columns) {
                    foreach ($columns as $col) {
                        $searchValue = $col['search']['value'] ?? '';
                        $dataKey = $col['data'] ?? '';

                        if ($searchValue && isset($item[$dataKey])) {
                            // kisbetűs összehasonlítás
                            if (stripos($item[$dataKey], $searchValue) === false) {
                                return false;
                            }
                        }
                    }
                    return true;
                });
            })
            ->make(true);
    }





}
