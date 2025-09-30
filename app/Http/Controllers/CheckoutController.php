<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use App\Models\Product;

class CheckoutController extends Controller
{
    public function index() {

        $customer = auth('customer')->user();
        $cart = $customer->cart;

        // ha benne van a kosarban a futarszolgaltatas, akkor toroljuk ki minden esetben
        $cart->items()->where('product_id', 1)->delete();

        $cart_items = $customer->cart()->with('items.product')->first();

        $gls_fee = Product::find(1)?->gross_price ?? 0; // GLS futarszolgaltatas termek

        return view('pages.checkout', [
            'cart' => auth('customer')->user()->cart,
            'billing_addresses' => auth('customer')->user()->billingAddresses,
            'shipping_addresses' => auth('customer')->user()->shippingAddresses,
            'cart_items' => $cart_items,
            'company_sites' => CompanySite::all(),
            'gls_fee' => $gls_fee,
        ]);
    }


}
