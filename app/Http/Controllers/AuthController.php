<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     *
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah, coba lagi ya!',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan halaman registrasi
     *
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi user baru
     * - Create user account
     * - Create default pockets (main, savings, emergency, wishlist)
     * - Auto-login user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'salary_date' => 1, // Default tanggal gajian
        ]);

        // Create default pockets
        $user->pockets()->createMany([
            ['name' => 'Dompet Harian', 'type' => 'main', 'balance' => 0],
            ['name' => 'Tabungan Emas', 'type' => 'savings', 'balance' => 0, 'target_amount' => 0],
            ['name' => 'Dana Darurat', 'type' => 'emergency', 'balance' => 0, 'target_amount' => 0],
            ['name' => 'Wishlist', 'type' => 'wishlist', 'balance' => 0, 'target_amount' => 0],
        ]);

        Auth::login($user);

        return redirect()->route('setup')->with('success', 'Akun berhasil dibuat! Sekarang atur dompetmu.');
    }

    /**
     * Tampilkan halaman setup awal (setelah registrasi)
     *
     * @return \Illuminate\View\View
     */
    public function showSetup()
    {
        $user = Auth::user();
        $pockets = $user->pockets;

        return view('auth.setup', compact('pockets'));
    }

    /**
     * Simpan konfigurasi awal user (tanggal gajian & saldo awal)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveSetup(Request $request)
    {
        $request->validate([
            'salary_date' => 'required|integer|min:1|max:31',
            'main_balance' => 'required|numeric|min:0',
            'savings_balance' => 'nullable|numeric|min:0',
            'emergency_balance' => 'nullable|numeric|min:0',
            'wishlist_balance' => 'nullable|numeric|min:0',
            'wishlist_target' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();

        // Update tanggal gajian
        $user->update([
            'salary_date' => $request->salary_date
        ]);

        // Update saldo pockets
        $user->pockets()->where('type', 'main')->update([
            'balance' => $request->main_balance
        ]);

        $user->pockets()->where('type', 'savings')->update([
            'balance' => $request->savings_balance ?? 0
        ]);

        $user->pockets()->where('type', 'emergency')->update([
            'balance' => $request->emergency_balance ?? 0
        ]);

        $user->pockets()->where('type', 'wishlist')->update([
            'balance' => $request->wishlist_balance ?? 0,
            'target_amount' => $request->wishlist_target ?? 0
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Setup awal selesai! Mulai kelola dompetmu sekarang.');
    }

    /**
     * Proses logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
