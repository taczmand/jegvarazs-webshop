<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\CompanySite;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Services\Order\PaymentHandlers\PaymentHandlerFactory;
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

        // Validált adatok kinyerése a requestből
        $validated = $request->validated();

        // Kosár termékek előkészítése
        $cartItems = $cart->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->product->title,
                'gross_price' => $item->product->gross_price,
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
                    'postal_code' => $billing->postal_code,
                    'city' => $billing->city,
                    'address_line' => $billing->address_line,
                    'tax_number' => $billing->tax_number,
                ];
            } else {
                $billingAddress = [
                    'name' => $validated['billing_name'],
                    'country' => $validated['billing_country'],
                    'postal_code' => $validated['billing_postal_code'],
                    'city' => $validated['billing_city'],
                    'address_line' => $validated['billing_address_line'],
                    'tax_number' => $validated['billing_tax_number'] ?? null,
                ];
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
                $selected_site_id = $request->input('selected_shipping_address');
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
                    'name' => $validated['shipping_name'],
                    'country' => $validated['shipping_country'],
                    'zip_code' => $validated['shipping_zip_code'],
                    'city' => $validated['shipping_city'],
                    'address_line' => $validated['shipping_address_line'],
                ];
            }

            $order = DB::transaction(function () use ($validated, $cartItems, $billingAddress, $shippingAddress) {
                $order = Order::create([
                    'customer_id' => auth('customer')->id(),
                    'contact_first_name' => $validated['customer_first_name'],
                    'contact_last_name' => $validated['customer_last_name'],
                    'contact_email' => $validated['customer_email'],
                    'contact_phone' => $validated['customer_phone'],
                    'billing_name' => $billingAddress['name'],
                    'billing_country' => $billingAddress['country'],
                    'billing_postal_code' => $billingAddress['postal_code'],
                    'billing_city' => $billingAddress['city'],
                    'billing_address_line' => $billingAddress['address_line'],
                    'billing_tax_number' => $billingAddress['tax_number'] ?? null,
                    'shipping_name' => $shippingAddress['name'],
                    'shipping_country' => $shippingAddress['country'],
                    'shipping_postal_code' => $shippingAddress['zip_code'],
                    'shipping_city' => $shippingAddress['city'],
                    'shipping_address_line' => $shippingAddress['address_line'],
                    'payment_method' => $validated['payment_method'],
                    'comment' => $validated['comment'] ?? null,
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

            // Kosár kiürítése (opcionális)
            $cart->items()->delete();

            // Fizetési handler kiválasztása és feldolgozása
            $handler = PaymentHandlerFactory::make($validated['payment_method']);

            if ($handler && method_exists($handler, 'handleRedirect')) {
                return $handler->handleRedirect($order);
            }



            return redirect()->route('checkout.success')->with('success', 'Rendelés sikeresen leadva!');
        } catch (\Throwable $e) {
            \Log::error('Hiba történt a rendelés mentésekor: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['Hiba történt a rendelés mentésekor.']);
        }
    }

}
