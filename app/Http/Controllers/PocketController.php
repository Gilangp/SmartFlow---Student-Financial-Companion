<?php

namespace App\Http\Controllers;

use App\Models\Pocket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PocketController extends Controller
{
    /**
     * Form tambah pocket (khusus wishlist)
     */
    public function create(Request $request)
    {
        abort_if($request->type !== 'wishlist', 404);

        return view('pockets.create');
    }

    /**
     * Simpan wishlist baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'target_amount' => 'required|numeric|min:10000',
            'balance' => 'nullable|numeric|min:0',
        ]);

        Pocket::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'type' => 'wishlist',
            'balance' => $request->balance ?? 0,
            'target_amount' => $request->target_amount,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Wishlist berhasil ditambahkan 🎯');
    }
}
