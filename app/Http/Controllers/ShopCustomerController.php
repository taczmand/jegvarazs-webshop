<?php

namespace App\Http\Controllers;

use App\Mail\InterestingInstallMail;
use App\Mail\InterestingProductMail;
use App\Models\BasicData;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Order\PaymentHandlers\PaymentHandlerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationSuccess;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ShopCustomerController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('index');
        }

        return view('pages.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $customer = Customer::where('email', $credentials['email'])
            ->where('status', 'active') // csak az aktívokat engedjük be
            ->first();

        if ($customer && Hash::check($credentials['password'], $customer->password)) {
            Auth::guard('customer')->login($customer);
            return redirect()->route('index');
        }

        // ha ide jutunk, vagy nem létező, vagy inaktív, vagy rossz jelszó
        return back()->withErrors(['email' => 'Hibás hitelesítési adatok vagy inaktív fiók.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        return redirect('/bejelentkezes');
    }

    public function showRegistrationForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('index');
        }

        return view('pages.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
            'is_partner' => 'sometimes|boolean',
            'fgaz' => 'required_if:is_partner,1|nullable|string|max:20',
        ]);

        $isPartner = !empty($data['is_partner']);

        $customer = Customer::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $isPartner ? 'inactive' : 'active',
            'is_partner' => $isPartner,
            'fgaz' => $data['fgaz'] ?? null,
        ]);

        Mail::to($customer->email)->send(new RegistrationSuccess($customer));

        if (!$isPartner) {
            Auth::guard('customer')->login($customer);
            return redirect()->route('index');
        }

        // Szerelő regisztráció: ne jelentkezzen be automatikusan
        return redirect()->route('customer.register.success');
    }

    public function sendEmail(Request $request)
    {
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'email_type' => 'required|string',
            'productID' => 'required|integer',
            'contact_message' => 'required|string',
        ]);

        $allowedEmailTypes = ['product-interesting', 'install-interesting'];

        if (!in_array($validated['email_type'], $allowedEmailTypes)) {
            return response()->json([
                'result' => 'error',
                'error_message' => 'Érvénytelen e-mail típus.',
            ], 422);
        }

        try {

            $product = Product::find($validated['productID']);

            $support_email = BasicData::where('key', 'support_email')->first();

            if ($support_email) {

                if ($validated['email_type'] === "product-interesting") {
                    \Log::info('Email interesting elküldve ide: '.$support_email->value, [$customer, $product, $validated['contact_message']]);
                    Mail::to($support_email->value)->send(new InterestingProductMail(
                        $customer,
                        $product,
                        $validated['contact_message']
                    ));
                }

                if ($validated['email_type'] === "install-interesting") {
                    \Log::info('Email install elküldve ide: '.$support_email->value, [$customer, $product, $validated['contact_message']]);
                    Mail::to($support_email->value)->send(new InterestingInstallMail(
                        $customer,
                        $product,
                        $validated['contact_message']
                    ));
                }
            } else {
                \Log::error('ShopCustomerController - sendEmail() -> Support email cím nincs beállítva');
            }

            return response()->json([
                'result' => 'success',
                'message' => 'E-mail sikeresen elküldve.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'result' => 'error',
                'error_message' => 'Nem sikerült elküldeni az e-mailt. (' . $e->getMessage() . ')'
            ], 500);
        }
    }

    public function showPasswordRequestForm()
    {
        return view('pages.email_reset');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Ellenőrzés, hogy létezik-e a customer ezzel az email címmel
        $customerExists = Customer::where('email', $request->input('email'))->exists();

        if (! $customerExists) {
            return back()->withErrors(['email' => 'Ez az email cím nem található az adatbázisban.']);
        }

        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'A jelszó-visszaállító link elküldve az email címedre.');
        } else {
            return back()->withErrors(['email' => 'Nem sikerült elküldeni a jelszó-visszaállító linket. Kérjük, próbáld újra.']);
        }
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('pages.reset', ['token' => $token, 'email' => $request->email]);
    }

    public function passwordReset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($customer) use ($request) {
                $customer->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                Auth::guard('customer')->login($customer); // beléptetés
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('index')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function orders()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a rendeléseid megtekintéséhez.']);
        }

        $orders = $customer->orders()->with('items')->orderBy('created_at', 'desc')->get();

        return view('pages.orders.index', compact('orders'));
    }

    public function orderShow()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a rendeléseid megtekintéséhez.']);
        }

        $orderId = request()->route('id');
        $order = $customer->orders()->with('items')->find($orderId);

        if (!$order) {
            return redirect()->route('customer.orders')->withErrors(['order' => 'A kiválasztott rendelés nem található.']);
        }

        return view('pages.orders.show', compact('order'));
    }

    public function orderDestroy()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a rendeléseid megtekintéséhez.']);
        }

        $orderId = request()->route('id');
        $order = $customer->orders()->find($orderId);

        if (!$order) {
            return redirect()->route('customer.orders')->withErrors(['order' => 'A kiválasztott rendelés nem található.']);
        }

        // Rendelés törlése
        $order->delete();

        return redirect()->route('customer.orders')->with('success', 'Rendelés sikeresen törölve.');
    }

    public function retryPayment()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a rendelésed újrafizetéséhez.']);
        }
        $orderId = request()->route('id');

        $order = Order::with('items')->find($orderId);

        return view('pages.orders.retry', compact('order'));
    }

    public function processRetryPayment(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a rendelésed újrafizetéséhez.']);
        }

        $orderId = $request->input('order_id');
        $order = Order::find($orderId);
        $cartItems = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->product->title,
                'gross_price' => $item->product->display_gross_price,
                'quantity' => $item->quantity,
                'tax_value' => $item->product->taxCategory->tax_value,
            ];
        });

        if (!$order || $order->customer_id !== $customer->id) {
            return redirect()->route('customer.orders')->withErrors(['order' => 'A kiválasztott rendelés nem található vagy nem a tiéd.']);
        }

        try {
            // Fizetési handler kiválasztása és feldolgozása
            $handler = PaymentHandlerFactory::make($request['payment_method']);

            if ($handler && method_exists($handler, 'handleRedirect')) {
                return $handler->handleRedirect($order, $cartItems);
            }
        } catch (\Exception $e) {
            \Log::error('Hiba történt a fizetés újrapróbálásakor: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            return redirect()->route('customer.orders')->withErrors(['payment' => 'Hibás fizetési mód vagy a fizetési folyamat sikertelen.']);
        }

    }

    public function profile()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a profilod megtekintéséhez.']);
        }

        $billingAddress = $customer->billingAddresses;
        $shippingAddress = $customer->shippingAddresses;

        return view('pages.profile', compact('customer', 'billingAddress', 'shippingAddress'));
    }
    public function profileUpdate(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('login')->withErrors(['login' => 'Kérjük, jelentkezz be a profilod frissítéséhez.']);
        }

        // Profil frissítése

        if ($request->has('profile_save')) {

            $data = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'required|string|email|max:255|unique:customers,email,' . $customer->id,
                'fgaz' => 'nullable|string|max:20',
            ]);

            // Ellenőrizzük, hogy a jelszó mező üres-e
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'required|string|min:8|confirmed',
                ]);
                // Csak akkor frissítjük a jelszót, ha a mező nem üres

                $data['password'] = Hash::make($request->input('password'));
            }

            try {
                $customer->update($data);
                return redirect()->route('customer.profile')
                    ->with('success', 'Profil sikeresen frissítve.');
            } catch (\Exception $e) {
                \Log::error('Profil mentés hiba: ' . $e->getMessage());

                return redirect()->back()
                    ->withErrors(['db' => 'A profil mentése közben hiba történt. Kérjük, próbáld újra.'])
                    ->withInput();
            }
        }

        // Számlázási cím frissítése

        if ($request->has('billing_save_id')) {
            $id = $request->input('billing_save_id');
            $addresses = $request->input('billing_addresses') ?? [];

            if (!isset($addresses[$id])) {
                return redirect()->back()->withErrors(['billing' => 'Hibás sor adatok.'])->withInput();
            }

            $data = $addresses[$id];

            // Validáció
            $validated = \Validator::make($data, [
                'billing_name' => 'required|string|max:255',
                'billing_tax_number' => 'required|string|max:20',
                'billing_country' => 'required|string|max:100',
                'billing_zip' => 'required|string|max:20',
                'billing_city' => 'required|string|max:100',
                'billing_address' => 'required|string|max:255',
            ])->validate();

            $updateData = [
                'name' => $validated['billing_name'],
                'tax_number' => $validated['billing_tax_number'],
                'country' => $validated['billing_country'],
                'zip' => $validated['billing_zip'],
                'city' => $validated['billing_city'],
                'address' => $validated['billing_address'],
            ];

            $billing = $customer->billingAddresses()->find($id);
            if ($billing) {
                $billing->update($updateData);
                return redirect()->back()->with('success', 'Számlázási cím sikeresen frissítve.');
            } else {
                return redirect()->back()->withErrors(['billing' => 'A számlázási cím nem található.']);
            }
        }

        // Számlázási cím törlése

        if ($request->has('billing_delete_id')) {
            $id = $request->input('billing_delete_id');
            $billing = $customer->billingAddresses()->find($id);
            if ($billing) {
                $billing->delete();
                return redirect()->back()->with('success', 'Számlázási cím törölve.');
            } else {
                return redirect()->back()->withErrors(['billing' => 'A számlázási cím nem található.']);
            }
        }

        // Szállítási cím frissítése

        if ($request->has('shipping_save_id')) {
            $id = $request->input('shipping_save_id');
            $addresses = $request->input('shipping_addresses') ?? [];

            if (!isset($addresses[$id])) {
                return redirect()->back()->withErrors(['billing' => 'Hibás sor adatok.'])->withInput();
            }

            $data = $addresses[$id];

            // Validáció
            $validated = \Validator::make($data, [
                'shipping_name' => 'required|string|max:255',
                'shipping_email' => 'required|string|max:255|email',
                'shipping_phone' => 'required|string|max:20',
                'shipping_country' => 'required|string|max:100',
                'shipping_zip' => 'required|string|max:20',
                'shipping_city' => 'required|string|max:100',
                'shipping_address' => 'required|string|max:255',
            ])->validate();

            $updateData = [
                'name' => $validated['shipping_name'],
                'email' => $validated['shipping_email'],
                'phone' => $validated['shipping_phone'],
                'country' => $validated['shipping_country'],
                'zip_code' => $validated['shipping_zip'],
                'city' => $validated['shipping_city'],
                'address_line' => $validated['shipping_address']
            ];

            $shipping = $customer->shippingAddresses()->find($id);
            if ($shipping) {
                $shipping->update($updateData);
                return redirect()->back()->with('success', 'Szállítási cím sikeresen frissítve.');
            } else {
                return redirect()->back()->withErrors(['shipping' => 'A szállítási cím nem található.']);
            }
        }

        // Szállítási cím törlése

        if ($request->has('shipping_delete_id')) {
            $id = $request->input('shipping_delete_id');
            $shipping = $customer->shippingAddresses()->find($id);
            if ($shipping) {
                $shipping->delete();
                return redirect()->back()->with('success', 'Szállítási cím törölve.');
            } else {
                return redirect()->back()->withErrors(['shipping' => 'A szállítási cím nem található.']);
            }
        }
    }
}
