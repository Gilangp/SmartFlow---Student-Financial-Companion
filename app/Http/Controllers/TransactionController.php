<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Pocket;
use App\Models\Transaction;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Tampilkan form input pengeluaran
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $pockets = $user->pockets;
        $categories = Category::where('user_id', $user->id)
                        ->orWhere('user_id', null)
                        ->get();

        return view('transactions.create', compact('pockets', 'categories'));
    }

    /**
     * Simpan pengeluaran baru dan update saldo pocket
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'category_id' => 'required|exists:categories,id',
            'pocket_id' => 'required|exists:pockets,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        // Gunakan DB transaction untuk memastikan data consistency
        DB::transaction(function () use ($request) {
            // Simpan transaksi
            Transaction::create([
                'user_id' => Auth::id(),
                'pocket_id' => $request->pocket_id,
                'category_id' => $request->category_id,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
                'type' => 'expense',
            ]);

            // Kurangi saldo pocket
            $pocket = Pocket::find($request->pocket_id);
            $pocket->decrement('balance', $request->amount);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Jajan berhasil dicatat!');
    }

    /**
     * Extract transaksi dari natural language menggunakan AI
     *
     * @param Request $request
     * @param GeminiService $ai
     * @return \Illuminate\Http\JsonResponse
     */
    public function smartInput(Request $request, GeminiService $ai)
    {
        $request->validate(['text' => 'required|string|min:3']);

        $data = $ai->extractTransaction($request->input('text'));

        return response()->json($data);
    }
}
