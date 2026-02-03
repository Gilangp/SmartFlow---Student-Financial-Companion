<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function create()
    {
        return view('incomes.create', [
            'pockets' => Auth::user()->pockets
        ]);
    }

    public function store(Request $request, TransactionService $service)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1000',
            'source'      => 'required|in:routine,bonus',
            'date'        => 'required|date',
            'allocations' => 'array',
        ]);

        try {
            $service->incomeSplit([
                'user_id'     => Auth::id(),
                'amount'      => $request->amount,
                'source'      => $request->source,
                'date'        => $request->date,
                'allocations' => $request->input('allocations', []),
            ]);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['amount' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Alhamdulillah! Pemasukan berhasil dicatat');
    }
}
