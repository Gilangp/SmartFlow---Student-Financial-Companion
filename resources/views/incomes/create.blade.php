@extends('layouts.app')

@section('title', 'Catat Pemasukan - SmartFlow')

@section('content')

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}" class="bg-white p-2 rounded-full shadow-sm text-gray-600 hover:bg-gray-100">
            ⬅️
        </a>
        <h2 class="text-xl font-bold text-gray-800">Catat Pemasukan</h2>
    </div>

    {{-- Income Form with Pay Yourself First Strategy --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
         x-data="{
            totalAmount: 0,
            allocations: {},

            // Hitung sisa uang yang masuk ke Dompet Utama
            get remaining() {
                let allocated = 0;
                for (const key in this.allocations) {
                    allocated += parseInt(this.allocations[key] || 0);
                }
                return Math.max(0, this.totalAmount - allocated);
            }
         }">

        <form action="{{ route('incomes.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Total Amount Input --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Terima Uang (Rp)</label>
                <input type="number" name="amount" x-model="totalAmount" required placeholder="0"
                    class="w-full p-4 bg-green-50 rounded-xl border border-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 text-2xl font-bold text-green-700">
            </div>

            {{-- Income Source --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sumber</label>
                <select name="source" class="w-full p-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="routine">Rutin (Bulanan/Kiriman)</option>
                    <option value="bonus">Bonus / THR / Project</option>
                </select>
            </div>

            <hr class="border-dashed border-gray-200">

            {{-- Pay Yourself First: Allocate to Savings --}}
            <div>
                <h3 class="text-md font-bold text-gray-800 mb-2">💸 Sisihkan di Awal (Pay Yourself First)</h3>
                <p class="text-xs text-gray-500 mb-4">Isi nominal di bawah ini untuk langsung masuk tabungan.</p>

                <div class="space-y-3">
                    @foreach($pockets as $pocket)
                        @if($pocket->type !== 'main')
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-lg">
                                {{ $pocket->type == 'emergency' ? '🛡️' : ($pocket->type == 'wishlist' ? '👟' : '📈') }}
                            </div>
                            <div class="flex-1">
                                <label class="text-xs font-semibold text-gray-600">{{ $pocket->name }}</label>
                                <input type="number" name="allocations[{{ $pocket->id }}]"
                                       x-model="allocations[{{ $pocket->id }}]" placeholder="0"
                                       class="w-full p-2 bg-gray-50 rounded-lg border border-gray-200 text-sm focus:ring-green-500">
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Remaining Balance Display --}}
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                <p class="text-sm text-blue-800">Sisa yang masuk ke <strong>Dompet Harian</strong>:</p>
                <p class="text-xl font-bold text-blue-700" x-text="'Rp ' + parseInt(remaining).toLocaleString('id-ID')"></p>
                <p class="text-xs text-blue-500 mt-1" x-show="remaining > 0">
                    *Akan menambah jatah jajan harianmu!
                </p>
            </div>

            <input type="hidden" name="date" value="{{ date('Y-m-d') }}">

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-md transition transform active:scale-95">
                Simpan Pemasukan
            </button>
        </form>
    </div>

@endsection
