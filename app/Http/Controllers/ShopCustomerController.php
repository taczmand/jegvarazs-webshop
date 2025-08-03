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
}
