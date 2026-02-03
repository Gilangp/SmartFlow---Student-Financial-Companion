<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Auth\SetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    protected SetupService $setupService;

    public function __construct(SetupService $setupService)
    {
        $this->setupService = $setupService;
    }

    /**
     * Halaman login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah'])
            ->onlyInput('email');
    }

    /**
     * Halaman register
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {

            $user = User::create([
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'password'    => $validated['password'],
                'salary_date' => 1,
            ]);

            $user->pockets()->createMany([
                ['name' => 'Dompet Harian', 'type' => 'main'],
                ['name' => 'Tabungan Emas', 'type' => 'savings', 'is_locked' => true],
                ['name' => 'Dana Darurat',  'type' => 'emergency', 'is_locked' => true],
                ['name' => 'Wishlist',      'type' => 'wishlist'],
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()
            ->route('setup')
            ->with('success', 'Akun berhasil dibuat! Sekarang atur dompetmu.');
    }

    /**
     * Halaman setup awal
     */
    public function showSetup()
    {
        return view('auth.setup', [
            'pockets' => Auth::user()->pockets,
        ]);
    }

    /**
     * Simpan setup awal
     */
    public function saveSetup(Request $request)
    {
        $validated = $request->validate([
            'salary_date'       => 'required|integer|min:1|max:31',
            'main_balance'      => 'required|numeric|min:0',
            'savings_balance'   => 'nullable|numeric|min:0',
            'emergency_balance' => 'nullable|numeric|min:0',
        ]);

        $this->setupService->handle(Auth::user(), $validated);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Setup awal selesai!');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
