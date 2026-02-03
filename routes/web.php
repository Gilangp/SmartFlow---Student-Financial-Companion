<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\IncomeController;

/**
 * SmartFlow Routes
 * Personal Finance Management Application
 */

// ============================================================
// 1. PUBLIC ROUTES (Landing Page)
// ============================================================
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing-page');
})->name('home');


// ============================================================
// 2. GUEST ROUTES (Belum Login)
// ============================================================
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});


// ============================================================
// 3. PROTECTED ROUTES (Sudah Login)
// ============================================================
Route::middleware(['auth'])->group(function () {

    // --- AUTHENTICATION ---
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // --- SETUP AWAL (After Registration) ---
    Route::get('/setup-awal', [AuthController::class, 'showSetup'])
        ->name('setup');
    Route::post('/setup-awal', [AuthController::class, 'saveSetup'])
        ->name('setup.store');

    // --- DASHBOARD & AI ADVICE ---
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/dashboard/advice', [DashboardController::class, 'getAdvice'])
        ->name('dashboard.advice');

    // --- TRANSAKSI (PENGELUARAN) ---
    Route::prefix('/catat-jajan')->group(function () {
        Route::get('/', [TransactionController::class, 'create'])
            ->name('transactions.create');
        Route::post('/', [TransactionController::class, 'store'])
            ->name('transactions.store');
        Route::post('/smart', [TransactionController::class, 'smartInput'])
            ->name('transactions.smart');
    });

    // --- PEMASUKAN (INCOME) ---
    Route::prefix('/pemasukan')->group(function () {
        Route::get('/', [IncomeController::class, 'create'])
            ->name('incomes.create');
        Route::post('/', [IncomeController::class, 'store'])
            ->name('incomes.store');
    });

});
