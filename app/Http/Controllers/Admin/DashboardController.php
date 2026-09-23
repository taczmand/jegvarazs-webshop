<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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

    public function huPublicHolidays(Request $request)
    {
        $year = (int) $request->query('year');
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        $cacheKey = 'hu_public_holidays_' . $year;

        $data = Cache::remember($cacheKey, now()->addDays(14), function () use ($year) {
            $url = 'https://date.nager.at/api/v3/PublicHolidays/' . $year . '/HU';
            $resp = Http::timeout(8)->get($url);

            if (!$resp->ok()) {
                return [];
            }

            $json = $resp->json();
            return is_array($json) ? $json : [];
        });

        return response()->json([
            'year' => $year,
            'holidays' => $data,
        ]);
    }
}
