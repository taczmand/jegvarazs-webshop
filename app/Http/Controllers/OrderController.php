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

        if (!$request->input('order_condition')) {
            return redirect()->back()
                ->withErrors(['El kell fogadni az adatkezelési nyilatkozatot.'])
                ->withInput();
        }

        // Kosár ellenőrzése
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()
                ->withErrors(['Hiba történt a rendelés mentésekor.'])
                ->withInput();
        }

        $total_item_amount = 0;
        $subtotal = 0;
        if(0 < $cart->items->count()) {
            foreach($cart->items as $item) {
                $subtotal = $item->product->display_gross_price * $item->quantity;
                $total_item_amount += $subtotal;
            }
        }

        // Futárszolgálat díj hozzáadása a kosárhoz, ha nem helyi átvétel (egyenlőre csak GLS)
        if ($request->input('shipping_choice') !== 'local') {

            $shipping_methods = config('shipping_methods');
            $gls = collect($shipping_methods)->firstWhere('code', 'gls');
            $cost_limit = $gls['cost_limit'] ?? 0;

            if ($total_item_amount < $cost_limit) {
                if (!$cart->items->contains('product_id', 1)) {
                    $cart->items()->create([
                        'product_id' => 1,
                        'quantity' => 1,
                    ]);
                }
            }
        }

        $cart->refresh();

        // Kosár termékek előkészítése
        $cartItems = $cart->items->map(function ($item) {
            return [
                'product_id'   => $item->product_id,
                'name'         => $item->product->title,
                'gross_price'  => $item->product->display_gross_price,
                'quantity'     => $item->quantity,
                'tax_value'    => $item->product->taxCategory->tax_value,
            ];
        });

        // ====== Számlázási cím ======
        if ($request->input('billing_choice') === 'exist') {
            $billing = $customer->billingAddresses()->findOrFail($request->input('selected_billing_address'));
            $billingAddress = [
                'name'        => $billing->name,
                'country'     => $billing->country,
                'zip_code'    => $billing->zip_code,
                'city'        => $billing->city,
                'address_line'=> $billing->address_line,
                'tax_number'  => $billing->tax_number,
            ];
        } else {
            $validatedBilling = $request->validate([
                'billing_name' => 'required|string|max:255',
                'billing_postal_code' => 'required|string|max:20',
                'billing_city' => 'required|string|max:255',
                'billing_address' => 'required|string|max:255',
                'billing_tax_number' => 'nullable|string|max:50',
                'billing_country' => 'required|string|size:2',
            ], [
                'billing_name.required' => 'A számlázási név megadása kötelező!',
                'billing_postal_code.required' => 'A számlázási irányítószám megadása kötelező!',
                'billing_city.required' => 'A számlázási város megadása kötelező!',
                'billing_address.required' => 'A számlázási cím megadása kötelező!',
            ]);

            $billingAddress = [
                'name'        => $validatedBilling['billing_name'],
                'country'     => $validatedBilling['billing_country'],
                'zip_code'    => $validatedBilling['billing_postal_code'],
                'city'        => $validatedBilling['billing_city'],
                'address_line'=> $validatedBilling['billing_address'],
                'tax_number'  => $validatedBilling['billing_tax_number'] ?? null,
            ];

            $customer->billingAddresses()->create($billingAddress);
        }

        // ====== Szállítási cím ======
        if ($request->input('shipping_choice') === 'exist') {
            $shipping = $customer->shippingAddresses()->findOrFail($request->input('selected_shipping_address'));
            $shippingAddress = [
                'name'        => $shipping->name,
                'country'     => $shipping->country,
                'zip_code'    => $shipping->zip_code,
                'city'        => $shipping->city,
                'address_line'=> $shipping->address_line,
            ];
        } elseif ($request->input('shipping_choice') === 'local') {
            $site = CompanySite::findOrFail($request->input('selected_local_shipping_address'));
            $shippingAddress = [
                'name'        => $site->name,
                'country'     => $site->country,
                'zip_code'    => $site->zip_code,
                'city'        => $site->city,
                'address_line'=> $site->address_line,
            ];
        } else {
            $validatedShipping = $request->validate([
                'shipping_name'        => 'required|string|max:255',
                'shipping_country'     => 'required|string|size:2',
                'shipping_zip'         => 'required|string|max:20',
                'shipping_city'        => 'required|string|max:255',
                'shipping_address_line'=> 'required|string|max:255',
            ], [
                'shipping_name.required' => 'A szállítási név megadása kötelező!',
                'shipping_country.required' => 'A szállítási ország megadása kötelező!',
                'shipping_zip.required' => 'A szállítási irányítószám megadása kötelező!',
                'shipping_city.required' => 'A szállítási város megadása kötelező!',
                'shipping_address_line.required' => 'A szállítási cím megadása kötelező!',
            ]);

            $shippingAddress = [
                'name'        => $validatedShipping['shipping_name'],
                'country'     => $validatedShipping['shipping_country'],
                'zip_code'    => $validatedShipping['shipping_zip'],
                'city'        => $validatedShipping['shipping_city'],
                'address_line'=> $validatedShipping['shipping_address_line'],
            ];

            $customer->shippingAddresses()->create($shippingAddress);
        }

        /*return redirect()->back()->with('debug', [
            'inputs' => $request->all(),
            'billing' => $billingAddress,
            'shipping' => $shippingAddress,
            'cartItems' => $cartItems->toArray(),
        ]);*/

        try {
            // ====== Rendelés mentése ======
            $order = DB::transaction(function () use ($cart, $request, $cartItems, $billingAddress, $shippingAddress) {
                $order = Order::create([
                    'customer_id'         => auth('customer')->id(),
                    'contact_first_name'  => $request['customer_first_name'],
                    'contact_last_name'   => $request['customer_last_name'],
                    'contact_email'       => $request['customer_email'],
                    'contact_phone'       => $request['customer_phone'],
                    'billing_name'        => $billingAddress['name'],
                    'billing_country'     => $billingAddress['country'],
                    'billing_postal_code' => $billingAddress['zip_code'],
                    'billing_city'        => $billingAddress['city'],
                    'billing_address_line'=> $billingAddress['address_line'],
                    'billing_tax_number'  => $billingAddress['tax_number'] ?? null,
                    'shipping_name'       => $shippingAddress['name'],
                    'shipping_country'    => $shippingAddress['country'],
                    'shipping_postal_code'=> $shippingAddress['zip_code'],
                    'shipping_city'       => $shippingAddress['city'],
                    'shipping_address_line'=> $shippingAddress['address_line'],
                    'payment_method'      => $request['payment_method'],
                    'comment'             => $request['comment'] ?? null,
                    'status'              => 'pending',
                ]);

                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item['product_id'],
                        'product_name' => $item['name'],
                        'quantity'     => $item['quantity'],
                        'gross_price'  => $item['gross_price'],
                        'tax_value'    => $item['tax_value'],
                    ]);
                }

                OrderHistory::create([
                    'order_id'   => $order->id,
                    'customer_id'=> auth('customer')->id(),
                    'action'     => 'order_created',
                    'data'       => json_encode([
                        'order' => $order->toArray(),
                        'items' => $cartItems,
                    ]),
                ]);

                return $order;
            });

            // ====== Fizetés ======
            $handler = PaymentHandlerFactory::make($request['payment_method']);
            if ($handler && method_exists($handler, 'handleRedirect')) {
                return $handler->handleRedirect($order, $cartItems);
            }

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'A rendelés sikeresen rögzítve lett.');

        } catch (\Throwable $e) {
            \Log::error('Hiba történt a rendelés mentésekor: ' . $e->getMessage(), [
                'exception' => $e,
                'request'   => $request->all(),
            ]);
            return redirect()->back()->withErrors([
                'Hiba történt a rendelés mentésekor.'
            ]);
        }
    }


}
