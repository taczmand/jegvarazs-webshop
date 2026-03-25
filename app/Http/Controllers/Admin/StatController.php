<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Searched;
use App\Models\WatchedProduct;
use App\Models\Worksheet;
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
                'user_actions.model_id',
                'user_actions.data',
                'user_actions.created_at',
            ])
            ->orderBy('user_actions.created_at', 'desc');

        return \DataTables::of($items)
            ->filter(function ($query) {
                $dtColumns = (array) request('columns', []);
                $columnSearchByData = function (string $dataName) use ($dtColumns): string {
                    foreach ($dtColumns as $col) {
                        if (!is_array($col)) {
                            continue;
                        }
                        if (($col['data'] ?? null) !== $dataName) {
                            continue;
                        }
                        $value = $col['search']['value'] ?? '';
                        return is_string($value) ? trim($value) : '';
                    }
                    return '';
                };

                $userName = request('user_name');
                if (!is_string($userName) || trim($userName) === '') {
                    $userName = $columnSearchByData('user_name');
                }

                if (is_string($userName) && trim($userName) !== '') {
                    $query->where('users.name', 'like', '%' . $userName . '%');
                }

                $model = request('model');
                if (!is_string($model) || trim($model) === '') {
                    $model = $columnSearchByData('model');
                }

                if (is_string($model) && trim($model) !== '') {
                    $directModels = [
                        'users',
                        'customers',
                        'orders',
                        'products',
                        'categories',
                        'contracts',
                        'offers',
                        'appointments',
                        'worksheets',
                        'brands',
                        'tags',
                        'attributes',
                        'blog_posts',
                        'basic_data',
                        'basic_media',
                        'automated_emails',
                        'appointment_photos',
                        'carts',
                        'cart_items',
                        'clients',
                        'client_addresses',
                        'company_sites',
                        'contract_products',
                        'coupons',
                        'customer_billing_addresses',
                        'customer_shipping_addresses',
                        'downloads',
                        'employees',
                        'leads',
                        'newsletter_subscriptions',
                        'offer_products',
                        'order_histories',
                        'order_items',
                        'partner_products',
                        'product_photos',
                        'searcheds',
                        'watched_products',
                        'worksheet_images',
                        'worksheet_products',
                        'worksheet_workers',
                        'shipping_methods',
                        'payment_methods',
                        'stock_statuses',
                        'order_statuses',
                        'tax_categories',
                    ];

                    if (in_array($model, $directModels, true)) {
                        $query->where('user_actions.model', '=', $model);
                    } else {
                    $modelMap = [
                        'users' => 'Felhasználók',
                        'customers' => 'Vevők',
                        'orders' => 'Rendelések',
                        'products' => 'Termékek',
                        'categories' => 'Kategóriák',
                        'contracts' => 'Szerződések',
                        'offers' => 'Ajánlatok',
                        'appointments' => 'Időpontok',
                        'worksheets' => 'Munkalapok',
                        'brands' => 'Gyártók',
                        'tags' => 'Címkék',
                        'attributes' => 'Tulajdonságok',
                        'blog_posts' => 'Blog bejegyzések',
                        'basic_data' => 'Alapadatok',
                        'basic_media' => 'Média',
                        'automated_emails' => 'E-mail automatizáció',
                        'appointment_photos' => 'Időpont képek',
                        'carts' => 'Kosarak',
                        'cart_items' => 'Kosár tételek',
                        'clients' => 'Ügyfelek',
                        'client_addresses' => 'Ügyfél címek',
                        'company_sites' => 'Telephelyek',
                        'contract_products' => 'Szerződés termékek',
                        'coupons' => 'Kuponok',
                        'customer_billing_addresses' => 'Számlázási címek',
                        'customer_shipping_addresses' => 'Szállítási címek',
                        'downloads' => 'Letöltések',
                        'employees' => 'Munkatársak',
                        'leads' => 'Érdeklődők',
                        'newsletter_subscriptions' => 'Hírlevél feliratkozások',
                        'offer_products' => 'Ajánlat termékek',
                        'order_histories' => 'Rendelés előzmények',
                        'order_items' => 'Rendelés tételek',
                        'partner_products' => 'Partner termékek',
                        'product_photos' => 'Termék képek',
                        'searched' => 'Keresések',
                        'watched_products' => 'Megtekintett termékek',
                        'worksheet_images' => 'Munkalap képek',
                        'worksheet_products' => 'Munkalap termékek',
                        'worksheet_workers' => 'Munkalap munkatársak',
                        'shipping_methods' => 'Szállítási módok',
                        'payment_methods' => 'Fizetési módok',
                        'stock_statuses' => 'Raktári állapotok',
                        'order_statuses' => 'Rendelési állapotok',
                        'tax_categories' => 'Adó osztályok',
                    ];

                    $needle = mb_strtolower((string) $model);
                    $matchingKeys = [];
                    foreach ($modelMap as $k => $v) {
                        if (mb_strpos(mb_strtolower($k), $needle) !== false || mb_strpos(mb_strtolower($v), $needle) !== false) {
                            $matchingKeys[] = $k;
                        }
                    }

                    if (count($matchingKeys) > 0) {
                        $query->whereIn('user_actions.model', $matchingKeys);
                    } else {
                        $query->where('user_actions.model', 'like', '%' . $model . '%');
                    }
                    }
                }

                $action = request('action');
                if (!is_string($action) || trim($action) === '') {
                    $action = $columnSearchByData('action');
                }

                if (is_string($action) && trim($action) !== '') {
                    $directActions = ['created', 'updated', 'deleted'];
                    if (in_array($action, $directActions, true)) {
                        $query->where('user_actions.action', '=', $action);
                    } else {
                    $actionMap = [
                        'created' => 'Létrehozott',
                        'updated' => 'Frissített',
                        'deleted' => 'Törölt',
                    ];

                    $needle = mb_strtolower((string) $action);
                    $matchingKeys = [];
                    foreach ($actionMap as $k => $v) {
                        if (mb_strpos(mb_strtolower($k), $needle) !== false || mb_strpos(mb_strtolower($v), $needle) !== false) {
                            $matchingKeys[] = $k;
                        }
                    }

                    if (count($matchingKeys) > 0) {
                        $query->whereIn('user_actions.action', $matchingKeys);
                    } else {
                        $query->where('user_actions.action', 'like', '%' . $action . '%');
                    }
                    }
                }

                $record = request('record');
                if (!is_string($record) || trim($record) === '') {
                    $record = $columnSearchByData('record');
                }

                if (is_string($record) && trim($record) !== '') {
                    $record = trim($record);
                    $numeric = preg_replace('/[^0-9]/', '', $record);

                    $query->where(function ($q) use ($record, $numeric) {
                        if ($numeric !== '') {
                            $q->orWhere('user_actions.model_id', '=', (int) $numeric);
                        }

                        $q->orWhere('user_actions.data', 'like', '%' . $record . '%');
                    });
                }

                $data = request('data');
                if (!is_string($data) || trim($data) === '') {
                    $data = $columnSearchByData('data');
                }

                if (is_string($data) && trim($data) !== '') {
                    $query->where('user_actions.data', 'like', '%' . $data . '%');
                }
            })
            ->editColumn('model', function ($row) {
                $map = [
                    'users' => 'Felhasználók',
                    'customers' => 'Vevők',
                    'orders' => 'Rendelések',
                    'products' => 'Termékek',
                    'categories' => 'Kategóriák',
                    'contracts' => 'Szerződések',
                    'offers' => 'Ajánlatok',
                    'appointments' => 'Időpontok',
                    'worksheets' => 'Munkalapok',
                    'brands' => 'Gyártók',
                    'tags' => 'Címkék',
                    'attributes' => 'Tulajdonságok',
                    'blog_posts' => 'Blog bejegyzések',
                    'basic_data' => 'Alapadatok',
                    'basic_media' => 'Média',
                    'automated_emails' => 'E-mail automatizáció',
                    'appointment_photos' => 'Időpont képek',
                    'carts' => 'Kosarak',
                    'cart_items' => 'Kosár tételek',
                    'clients' => 'Ügyfelek',
                    'client_addresses' => 'Ügyfél címek',
                    'company_sites' => 'Telephelyek',
                    'contract_products' => 'Szerződés termékek',
                    'coupons' => 'Kuponok',
                    'customer_billing_addresses' => 'Számlázási címek',
                    'customer_shipping_addresses' => 'Szállítási címek',
                    'downloads' => 'Letöltések',
                    'employees' => 'Munkatársak',
                    'leads' => 'Érdeklődők',
                    'newsletter_subscriptions' => 'Hírlevél feliratkozások',
                    'offer_products' => 'Ajánlat termékek',
                    'order_histories' => 'Rendelés előzmények',
                    'order_items' => 'Rendelés tételek',
                    'partner_products' => 'Partner termékek',
                    'product_photos' => 'Termék képek',
                    'searched' => 'Keresések',
                    'watched_products' => 'Megtekintett termékek',
                    'worksheet_images' => 'Munkalap képek',
                    'worksheet_products' => 'Munkalap termékek',
                    'worksheet_workers' => 'Munkalap munkatársak',
                    'shipping_methods' => 'Szállítási módok',
                    'payment_methods' => 'Fizetési módok',
                    'stock_statuses' => 'Raktári állapotok',
                    'order_statuses' => 'Rendelési állapotok',
                    'tax_categories' => 'Adó osztályok',
                ];

                $m = (string) ($row->model ?? '');
                return $map[$m] ?? $m;
            })
            ->editColumn('action', function ($row) {
                $map = [
                    'created' => 'Létrehozott',
                    'updated' => 'Frissített',
                    'deleted' => 'Törölt',
                ];
                $a = (string) ($row->action ?? '');
                return $map[$a] ?? $a;
            })
            ->addColumn('record', function ($row) {
                $data = $row->data;
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }

                $id = $row->model_id ?? null;
                $label = null;

                if (is_array($data) && isset($data['_record']) && is_array($data['_record'])) {
                    $id = $data['_record']['id'] ?? $id;
                    $label = $data['_record']['label'] ?? null;
                }

                if (!empty($id) && !empty($label)) {
                    return '#' . $id . ' – ' . $label;
                }

                if (!empty($id)) {
                    return '#' . $id;
                }

                return '';
            })
            ->editColumn('data', function ($row) {
                $data = $row->data;
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }

                if (!is_array($data)) {
                    return $data;
                }

                $recordMeta = null;
                if (isset($data['_record']) && is_array($data['_record'])) {
                    $recordMeta = $data['_record'];
                }

                if (!array_key_exists('old', $data) && !array_key_exists('new', $data)) {
                    $data = [
                        'new' => $data,
                    ];
                }

                $old = isset($data['old']) && is_array($data['old']) ? $data['old'] : [];
                $new = isset($data['new']) && is_array($data['new']) ? $data['new'] : [];

                $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                sort($keys);

                $changes = [];
                foreach ($keys as $k) {
                    $before = $old[$k] ?? null;
                    $after = $new[$k] ?? null;
                    if ($before === $after) {
                        continue;
                    }
                    $changes[] = [
                        'field' => (string) $k,
                        'old' => $before,
                        'new' => $after,
                    ];
                }

                return [
                    'old' => $old,
                    'new' => $new,
                    'changes' => $changes,
                    '_record' => $recordMeta,
                ];
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
    public function installations()
    {
        return view('admin.statistics.installations');
    }
    public function installationsData()
    {
        $query = Worksheet::query()
            ->from('worksheets as w')
            ->leftJoin(
                DB::raw('
                    (
                        SELECT
                            email,
                            MAX(installation_date) as last_maintenance_date
                        FROM worksheets
                        WHERE work_type = "Karbantartás"
                        GROUP BY email
                    ) as m
                '),
                'm.email',
                '=',
                'w.email'
            )
            ->where('w.work_type', 'Szerelés')
            ->where('w.work_status', 'Lezárva')
            ->select([
                'w.id',
                'w.name',
                'w.email',
                'w.phone',
                'w.city',
                'w.installation_date',
                DB::raw('m.last_maintenance_date'),
                // 👉 Összefűzött cím
                DB::raw("
                    CONCAT_WS(
                        ' ',
                        w.zip_code,
                        w.city,
                        w.address_line
                    ) as address
                "),
                DB::raw('
                    DATEDIFF(
                        CURDATE(),
                        COALESCE(m.last_maintenance_date, w.installation_date)
                    ) as days_since_service
                ')
            ]);

        return datatables()->of($query)
            ->editColumn('last_maintenance_date', fn ($row) =>
            $row->last_maintenance_date
                ? date('Y-m-d', strtotime($row->last_maintenance_date))
                : null
            )

            ->make(true);
    }


}
