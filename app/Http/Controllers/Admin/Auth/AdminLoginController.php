<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // először megpróbáljuk lekérni a felhasználót
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Nincs ilyen felhasználó.']);
        }

        // ha nem aktív a státusz
        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'A fiók inaktív vagy letiltott.']);
        }

        // csak ha aktív, próbáljuk meg bejelentkeztetni
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Hibás adatok.']);
    }


    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        return redirect('/admin/bejelentkezes');
    }
}
