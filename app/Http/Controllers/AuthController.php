<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Merchant;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isSuperAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('merchant.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('login'));

        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['login' => 'Akun Anda dinonaktifkan sementara. Hubungi SuperAdmin Kurtech.']);
            }

            return $user->isSuperAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('merchant.dashboard');
        }

        // Fallback SSO: Otomatis sinkronisasi & verifikasi akun dari remote.kurtech.my.id (CT 102)
        try {
            $ssoEndpoints = [
                'http://10.10.10.249/api/internal/verify-user',
                'https://remote.kurtech.my.id/api/internal/verify-user',
            ];
            $ssoSecret = 'kurpay_sso_secret_2026';

            foreach ($ssoEndpoints as $url) {
                try {
                    $res = \Illuminate\Support\Facades\Http::timeout(3)
                        ->post($url, [
                            'secret' => $ssoSecret,
                            'login' => $loginInput,
                            'password' => $request->password,
                        ]);

                    if ($res->successful() && $res->json('success')) {
                        $remoteData = $res->json('user');

                        if (!$user) {
                            $user = User::where('email', $remoteData['email'])->first();
                        }

                        if (!$user) {
                            $uName = $remoteData['username'] ?? explode('@', $remoteData['email'])[0];
                            $uName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $uName);
                            if (User::where('username', $uName)->exists()) {
                                $uName = $uName . rand(10, 99);
                            }

                            $user = User::create([
                                'name' => $remoteData['name'],
                                'username' => $uName,
                                'email' => $remoteData['email'],
                                'phone' => $remoteData['phone'] ?? '',
                                'password' => Hash::make($request->password),
                                'role' => 'merchant',
                                'status' => 'active',
                            ]);

                            Merchant::create([
                                'user_id' => $user->id,
                                'business_name' => $user->name,
                                'balance' => 0,
                                'gateway_mode' => 'kurtech',
                                'fee_deposit_balance' => 0,
                                'status' => 'active',
                            ]);
                        } else {
                            $user->update([
                                'password' => Hash::make($request->password),
                                'status' => 'active',
                            ]);
                        }

                        Auth::login($user, $request->boolean('remember'));
                        $request->session()->regenerate();

                        return $user->isSuperAdmin()
                            ? redirect()->route('admin.dashboard')
                            : redirect()->route('merchant.dashboard');
                    }
                } catch (\Throwable $subE) {
                    continue;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('KurPay SSO Fallback Error: ' . $e->getMessage());
        }

        return back()->withErrors([
            'login' => 'Username/Email atau kata sandi tidak cocok.',
        ])->onlyInput('login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'business_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $username = $request->input('username') ?: explode('@', $request->email)[0];
        $username = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $username);
        if (User::where('username', $username)->exists()) {
            $username = $username . rand(10, 99);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'merchant',
            'status' => 'active',
        ]);

        // Auto-create Merchant Profile with API Credentials
        Merchant::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'balance' => 0,
            'gateway_mode' => 'kurtech',
            'fee_deposit_balance' => 0,
            'status' => 'active',
        ]);

        Auth::login($user);

        return redirect()->route('merchant.dashboard')->with('success', 'Selamat datang di Kurtech Pay! Gateway pembayaran Anda sudah aktif.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}