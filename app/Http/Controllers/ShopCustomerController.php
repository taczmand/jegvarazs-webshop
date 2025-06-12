<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}
