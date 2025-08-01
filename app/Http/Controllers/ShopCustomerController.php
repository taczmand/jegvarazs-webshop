<?php

namespace App\Http\Controllers;

use App\Mail\InterestingInstallMail;
use App\Mail\InterestingProductMail;
use App\Models\BasicData;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationSuccess;

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
}
