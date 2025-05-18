<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CouponService;

class CouponController extends Controller
{
    protected $coupon_service;

    public function __construct(CouponService $service)
    {
        $this->coupon_service = $service;
    }

    public function index()
    {
        return view('admin.sales.coupons');
    }
}
