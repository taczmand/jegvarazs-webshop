<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\CompanySite;
use App\Models\Order;
use App\Models\OrderedProduct;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Services\Order\PaymentHandlers\PaymentHandlerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(CheckoutRequest $request)
    {
        $customer = auth('customer')->user();
        $cart = $customer->cart;


        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()
                ->withErrors(['Hiba történt a rendelés mentésekor.'])
                ->withInput();
        }

        // Kosár termékek előkészítése
        $cartItems = $cart->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->product->title,
                'gross_price' => $item->product->display_gross_price,
                'quantity' => $item->quantity,
                'tax_value' => $item->product->taxCategory->tax_value,
            ];
        });

        try {

            // Számlázási cím kiválasztása
            if ($request->input('billing_choice') === 'exist') {
                $selected_billing_id = $request->input('selected_billing_address');
                $billing = $customer->billingAddresses()->findOrFail($selected_billing_id);
                $billingAddress = [
                    'name' => $billing->name,
                    'country' => $billing->country,
                    'zip_code' => $billing->zip_code,
                    'city' => $billing->city,
                    'address_line' => $billing->address_line,
                    'tax_number' => $billing->tax_number,
                ];
            } else {
                $billingAddress = [
                    'name' => $request['billing_name'],
                    'country' => $request['billing_country'],
                    'zip_code' => $request['billing_postal_code'],
                    'city' => $request['billing_city'],
                    'address_line' => $request['billing_address'],
                    'tax_number' => $request['billing_tax_number'] ?? null,
                ];

                // Új számlázási cím mentése
                $customer->billingAddresses()->create($billingAddress);
            }

            // Szállítási cím kiválasztása
            if ($request->input('shipping_choice') === 'exist') {
                $selected_shipping_id = $request->input('selected_shipping_address');
                $shipping = $customer->shippingAddresses()->findOrFail($selected_shipping_id);
                $shippingAddress = [
                    'name' => $shipping->name,
                    'country' => $shipping->country,
                    'zip_code' => $shipping->zip_code,
                    'city' => $shipping->city,
                    'address_line' => $shipping->address_line
                ];
            } elseif($request->input('shipping_choice') === 'local') {
                $selected_site_id = $request->input('selected_local_shipping_address');
                $site = CompanySite::findOrFail($selected_site_id);
                $shippingAddress = [
                    'name' => $site->name,
                    'country' => $site->country,
                    'zip_code' => $site->zip_code,
                    'city' => $site->city,
                    'address_line' => $site->address_line
                ];
            } else {

                $shippingAddress = [
                    'name' => $request['shipping_name'],
                    'country' => $request['shipping_country'],
                    'zip_code' => $request['shipping_zip'],
                    'city' => $request['shipping_city'],
                    'address_line' => $request['shipping_address_line'],
                ];

                // Új szállítási cím mentése
                $customer->shippingAddresses()->create($shippingAddress);
            }

            $required_fields = ['name', 'country', 'zip_code', 'city', 'address_line'];

            $billing_field_names = [
                'name' => 'Számlázási adatoknál név',
                'country' => 'Számlázási adatoknál ország',
                'zip_code' => 'Számlázási adatoknál irányítószám',
                'city' => 'Számlázási adatoknál város',
                'address_line' => 'Számlázási adatoknál cím',
            ];

            // Számlázási cím ellenőrzése
            foreach ($required_fields as $field) {
                if (empty($billingAddress[$field])) {
                    return redirect()->back()
                        ->withErrors([$field => $billing_field_names[$field] . ' mező kitöltése kötelező.'])
                        ->withInput();
                }
            }

            $shipping_field_names = [
                'name' => 'Szállítási adatoknál név',
                'country' => 'Szállítási adatoknál ország',
                'zip_code' => 'Szállítási adatoknál irányítószám',
                'city' => 'Szállítási adatoknál város',
                'address_line' => 'Szállítási adatoknál cím',
            ];

            // Szállítási cím ellenőrzése
            foreach ($required_fields as $field) {
                if (empty($shippingAddress[$field])) {
                    return redirect()->back()
                        ->withErrors([$field => $shipping_field_names[$field] . ' mező kitöltése kötelező.'])
                        ->withInput();
                }
            }

            // Rendelés mentési folyamat adatbázisba

            $order = DB::transaction(function () use ($cart, $request, $cartItems, $billingAddress, $shippingAddress) {
                $order = Order::create([
                    'customer_id' => auth('customer')->id(),
                    'contact_first_name' => $request['customer_first_name'],
                    'contact_last_name' => $request['customer_last_name'],
                    'contact_email' => $request['customer_email'],
                    'contact_phone' => $request['customer_phone'],
                    'billing_name' => $billingAddress['name'],
                    'billing_country' => $billingAddress['country'],
                    'billing_postal_code' => $billingAddress['zip_code'],
                    'billing_city' => $billingAddress['city'],
                    'billing_address_line' => $billingAddress['address_line'],
                    'billing_tax_number' => $billingAddress['tax_number'] ?? null,
                    'shipping_name' => $shippingAddress['name'],
                    'shipping_country' => $shippingAddress['country'],
                    'shipping_postal_code' => $shippingAddress['zip_code'],
                    'shipping_city' => $shippingAddress['city'],
                    'shipping_address_line' => $shippingAddress['address_line'],
                    'payment_method' => $request['payment_method'],
                    'comment' => $request['comment'] ?? null,
                    'status' => 'pending',
                ]);

                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'gross_price' => $item['gross_price'],
                        'tax_value' => $item['tax_value'],
                    ]);
                }

                OrderHistory::create([
                    'order_id' => $order->id,
                    'customer_id' => auth('customer')->id(),
                    'action' => 'order_created',
                    'data' => json_encode([
                        'order' => $order->toArray(),
                        'items' => $cartItems,
                    ]),
                ]);

                return $order;
            });

            // Fizetési handler kiválasztása és feldolgozása
            $handler = PaymentHandlerFactory::make($request['payment_method']);

            if ($handler && method_exists($handler, 'handleRedirect')) {
                return $handler->handleRedirect($order, $cartItems);
            }


        } catch (\Throwable $e) {
            \Log::error('Hiba történt a rendelés mentésekor: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['Hiba történt a rendelés mentésekor.']);
        }
    }

}
