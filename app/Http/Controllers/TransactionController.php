<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Form transfer antar pocket
     */
    public function create()
    {
        return view('transfers.create', [
            'pockets' => Auth::user()->pockets
        ]);
    }

    /**
     * Proses transfer antar pocket
     */
    public function store(Request $request, TransactionService $service)
    {
        $request->validate([
            'from_pocket_id' => 'required|exists:pockets,id',
            'to_pocket_id'   => 'required|exists:pockets,id',
            'amount'         => 'required|numeric|min:100',
            'date'           => 'required|date',
        ]);

        try {
            $service->transfer([
                'user_id'        => Auth::id(),
                'from_pocket_id' => $request->from_pocket_id,
                'to_pocket_id'   => $request->to_pocket_id,
                'amount'         => $request->amount,
                'date'           => $request->date,
            ]);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['amount' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transfer antar pocket berhasil 🔄');
    }

    /**
     * Smart Input (AI Parsing)
     */
    public function smartInput(Request $request, GeminiService $ai)
    {
        $request->validate([
            'text' => 'required|string|min:3'
        ]);

        return response()->json(
            $ai->extractTransaction($request->text)
        );
    }
}
