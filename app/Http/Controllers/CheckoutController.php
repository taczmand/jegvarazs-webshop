<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;

class CheckoutController extends Controller
{
    public function index() {

        $customer = auth('customer')->user();
        $cart_items = $customer->cart()->with('items.product')->first();

        return view('pages.checkout', [
            'cart' => auth('customer')->user()->cart,
            'billing_addresses' => auth('customer')->user()->billingAddresses,
            'shipping_addresses' => auth('customer')->user()->shippingAddresses,
            'cart_items' => $cart_items,
            'company_sites' => CompanySite::all()
        ]);
    }


}
