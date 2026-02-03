<?php

namespace App\Http\Controllers;

use App\Models\Pocket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncomeController extends Controller
{
    /**
     * Tampilkan form input pemasukan dengan alokasi ke berbagai pocket
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $pockets = $user->pockets;

        return view('incomes.create', compact('pockets'));
    }

    /**
     * Simpan pemasukan dengan alokasi otomatis ke pocket yang sesuai
     * - User bisa mengalokasikan ke emergency, savings, wishlist
     * - Sisa otomatis masuk ke dompet harian (main pocket)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'source' => 'required|in:routine,bonus',
            'date' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();
            $batchId = Str::uuid();

            $allocations = $request->input('allocations', []);
            $totalAllocated = 0;

            // Alokasi ke pocket sesuai input user
            foreach ($allocations as $pocketId => $nominal) {
                if ($nominal <= 0) continue;

                $totalAllocated += $nominal;

                Transaction::create([
                    'user_id' => $user->id,
                    'pocket_id' => $pocketId,
                    'category_id' => null,
                    'amount' => $nominal,
                    'date' => $request->date,
                    'description' => 'Pemasukan ' . ucfirst($request->source),
                    'type' => 'income',
                    'income_source' => $request->source,
                    'batch_id' => $batchId
                ]);

                Pocket::find($pocketId)->increment('balance', $nominal);
            }

            // Validasi total alokasi tidak melebihi uang masuk
            if ($totalAllocated > $request->amount) {
                throw new \Exception("Total alokasi melebihi uang masuk!");
            }

            // Sisa masuk ke dompet utama (main)
            $remaining = $request->amount - $totalAllocated;
            if ($remaining > 0) {
                $mainPocket = $user->pockets()->where('type', 'main')->first();

                Transaction::create([
                    'user_id' => $user->id,
                    'pocket_id' => $mainPocket->id,
                    'amount' => $remaining,
                    'date' => $request->date,
                    'description' => 'Sisa Pemasukan ke Dompet Harian',
                    'type' => 'income',
                    'income_source' => $request->source,
                    'batch_id' => $batchId
                ]);

                $mainPocket->increment('balance', $remaining);
            }
        });

        return redirect()->route('dashboard')
            ->with('success', 'Alhamdulillah! Pemasukan berhasil dicatat.');
    }
}
