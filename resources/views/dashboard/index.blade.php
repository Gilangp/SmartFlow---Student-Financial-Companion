@extends('layouts.app')

@section('title', 'Dashboard - SmartFlow')

@section('content')

    {{-- AI Financial Advice Section --}}
    <div x-data="{
            message: 'Sedang memantau dompetmu...',
            loading: true,
            init() {
                fetch('{{ route('dashboard.advice') }}')
                    .then(res => res.json())
                    .then(data => {
                        this.message = data.message;
                        this.loading = false;
                    })
                    .catch(() => {
                        this.message = 'Gagal terhubung ke asisten keuangan.';
                        this.loading = false;
                    });
            }
         }"
         class="bg-indigo-900 text-indigo-100 p-4 rounded-xl shadow-lg mb-6 flex items-start gap-4 relative overflow-hidden">

        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white opacity-10 rounded-full blur-xl"></div>

        <div class="bg-white/20 p-2.5 rounded-full flex-shrink-0">
            <span class="text-2xl">🤖</span>
        </div>

        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-300 mb-1">
                Kata Asisten Keuangan
            </h3>

            {{-- Loading Spinner --}}
            <div x-show="loading" class="animate-pulse flex space-x-2 items-center h-6">
                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce delay-75"></div>
                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce delay-150"></div>
            </div>

            {{-- Message Display --}}
            <p x-show="!loading" x-text="message" class="text-sm font-medium leading-relaxed italic" style="display: none;"></p>
        </div>
    </div>

    {{-- Daily Budget Card --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 text-center relative overflow-hidden mb-6">
        {{-- Status Bar (Green/Red) --}}
        <div class="absolute top-0 left-0 w-full h-1
            {{ $todayExpense > $dailyBudget ? 'bg-red-500' : 'bg-green-500' }}"></div>

        <p class="text-gray-500 text-sm mb-1">Boleh Jajan Hari Ini</p>

        <h2 class="text-4xl font-extrabold mb-2
            {{ $todayExpense > $dailyBudget ? 'text-red-600' : 'text-gray-800' }}">
            Rp {{ number_format($dailyBudget - $todayExpense, 0, ',', '.') }}
        </h2>

        <p class="text-xs text-gray-400">
            Sisa Bulan: <strong>{{ round($remainingDays) }} Hari</strong> •
            Saldo Utama: <strong>Rp {{ number_format($mainPocket->balance, 0, ',', '.') }}</strong>
        </p>

        {{-- Overbudget Warning --}}
        @if($todayExpense > $dailyBudget)
            <div class="mt-3 bg-red-50 text-red-600 text-xs py-1 px-2 rounded-lg inline-block font-semibold animate-pulse">
                🚨 Stop Jajan! Udah Overbudget!
            </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('transactions.create') }}" class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white py-3 rounded-xl font-semibold shadow-md flex justify-center items-center gap-2 transition transform">
            <span>➕</span> Catat Jajan
        </a>
        <a href="{{ route('incomes.create') }}" class="bg-green-600 hover:bg-green-700 active:scale-95 text-white py-3 rounded-xl font-semibold shadow-md flex justify-center items-center gap-2 transition transform">
            <span>💰</span> Pemasukan
        </a>
    </div>

    {{-- Pocket Cards Section --}}
    <div>
        <h3 class="text-lg font-bold text-gray-700 mb-3">Kantong Uang</h3>
        <div class="space-y-3">

            {{-- Emergency Pocket --}}
            @include('components.pocket-card', ['pocket' => $emergencyPocket, 'icon' => '🛡️', 'color' => 'red'])

            {{-- Wishlist Pocket --}}
            @include('components.pocket-card', ['pocket' => $wishlistPocket, 'icon' => '👟', 'color' => 'purple'])

            {{-- Savings Pocket (Fixed Display) --}}
            <div class="bg-white p-4 rounded-xl border border-gray-200 flex justify-between items-center shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center text-xl">📈</div>
                    <div>
                        <p class="font-semibold text-gray-700">{{ $savingsPocket->name }}</p>
                        <p class="text-xs text-gray-500">Uang Dingin</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-800">Rp {{ number_format($savingsPocket->balance, 0, ',', '.') }}</p>
                    <p class="text-xs text-green-600 font-semibold">+ Aman</p>
                </div>
            </div>

        </div>
    </div>

@endsection
