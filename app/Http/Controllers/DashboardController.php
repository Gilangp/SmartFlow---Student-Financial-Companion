<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\GeminiService;
use App\Models\Pocket;

class DashboardController extends Controller
{
    /**
     * Hitung sisa hari sampai tanggal gajian berikutnya
     *
     * @return int Jumlah hari tersisa (minimum 1)
     */
    private function calculateRemainingDays(): int
    {
        $today = Carbon::now();
        $salaryDate = Auth::user()->salary_date ?? 1;

        $paydayThisMonth = Carbon::now()->setDay($salaryDate);

        if ($today->day >= $salaryDate) {
            $nextPayday = $paydayThisMonth->copy()->addMonth();
        } else {
            $nextPayday = $paydayThisMonth;
        }

        $remainingDays = $today->diffInDays($nextPayday);
        return $remainingDays == 0 ? 1 : $remainingDays;
    }

    /**
     * Tampilkan dashboard dengan budget dan pocket data
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $mainPocket = Pocket::where('user_id', $user->id)->where('type', 'main')->first();
        $emergencyPocket = Pocket::where('user_id', $user->id)->where('type', 'emergency')->first();
        $savingsPocket = Pocket::where('user_id', $user->id)->where('type', 'savings')->first();
        $wishlistPocket = Pocket::where('user_id', $user->id)->where('type', 'wishlist')->first();

        $today = Carbon::now();
        $remainingDays = $this->calculateRemainingDays();

        // Hitung jatah harian dari saldo utama dibagi sisa hari
        $dailyBudget = $mainPocket && $mainPocket->balance > 0
            ? $mainPocket->balance / $remainingDays
            : 0;

        // Hitung total pengeluaran hari ini
        $todayExpense = $mainPocket
            ? $mainPocket->transactions()
                ->whereDate('date', $today)
                ->where('type', 'expense')
                ->sum('amount')
            : 0;

        return view('dashboard.index', compact(
            'user', 'mainPocket', 'emergencyPocket',
            'savingsPocket', 'wishlistPocket', 'remainingDays',
            'dailyBudget', 'todayExpense'
        ));
    }

    /**
     * Fetch AI roasting untuk kondisi finansial user
     *
     * @param GeminiService $ai
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdvice(GeminiService $ai)
    {
        $user = Auth::user();
        $mainPocket = Pocket::where('user_id', $user->id)
                            ->where('type', 'main')
                            ->first();

        // Ambil 5 transaksi terakhir untuk konteks
        $transactions = $user->transactions()->latest()->take(5)->get();

        $remainingDays = $this->calculateRemainingDays();
        $mainBalance = $mainPocket?->balance ?? 0;

        // Jatah harian = saldo utama / sisa hari
        $dailyBudget = $mainBalance > 0 ? $mainBalance / $remainingDays : 0;

        $message = $ai->analyzeFinance(
            $user,
            $transactions,
            $dailyBudget,
            $mainBalance,
            $remainingDays
        );

        return response()->json(['message' => $message]);
    }
}
