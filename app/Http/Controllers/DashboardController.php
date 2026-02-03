<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\GeminiService;

class DashboardController extends Controller
{
    /**
     * Hitung sisa hari sampai gajian berikutnya
     */
    private function remainingDays(): int
    {
        $user       = Auth::user();
        $today      = Carbon::today();
        $salaryDate = $user->salary_date ?? 1;

        $nextPayday = Carbon::create(
            $today->year,
            $today->month,
            min($salaryDate, $today->daysInMonth)
        );

        if ($today->greaterThanOrEqualTo($nextPayday)) {
            $nextPayday->addMonth();
        }

        return max(1, $today->diffInDays($nextPayday));
    }

    /**
     * Dashboard utama
     */
    public function index()
    {
        $user = Auth::user();

        $pockets = $user->pockets->groupBy('type');

        $mainPocket      = $pockets->get('main')?->first();
        $savingsPocket   = $pockets->get('savings')?->first();
        $emergencyPocket = $pockets->get('emergency')?->first();
        $wishlistPockets = $pockets->get('wishlist') ?? collect();

        $remainingDays = $this->remainingDays();

        $dailyBudget = ($mainPocket && $mainPocket->balance > 0)
            ? $mainPocket->balance / $remainingDays
            : 0;

        $todayExpense = $mainPocket
            ? $mainPocket->transactions()
                ->whereDate('date', Carbon::today())
                ->where('type', 'expense')
                ->sum('amount')
            : 0;

        return view('dashboard.index', compact(
            'user',
            'mainPocket',
            'savingsPocket',
            'emergencyPocket',
            'wishlistPockets',
            'remainingDays',
            'dailyBudget',
            'todayExpense'
        ));
    }

    /**
     * AI Financial Advice (hari ini saja)
     */
    public function getAdvice(GeminiService $ai)
    {
        $user = Auth::user();

        $mainPocket = $user->pockets()
            ->where('type', 'main')
            ->first();

        $remainingDays = $this->remainingDays();
        $mainBalance   = $mainPocket?->balance ?? 0;

        $dailyBudget = $mainBalance > 0
            ? $mainBalance / $remainingDays
            : 0;

        $todayExpense = $mainPocket
            ? $mainPocket->transactions()
                ->whereDate('date', Carbon::today())
                ->where('type', 'expense')
                ->sum('amount')
            : 0;

        $transactions = $mainPocket
            ? $mainPocket->transactions()
                ->whereDate('date', Carbon::today())
                ->latest()
                ->take(3)
                ->get()
            : collect();

        $message = $ai->analyzeFinance(
            $user,
            $todayExpense,
            $dailyBudget,
            $mainBalance,
            $remainingDays,
            $transactions
        );

        return response()->json([
            'message' => $message
        ]);
    }
}
