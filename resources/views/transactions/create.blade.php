@extends('layouts.app')

@section('title', 'Catat Pengeluaran')

@section('content')

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('dashboard') }}" class="bg-white p-3 rounded-full shadow-sm text-gray-600 hover:bg-gray-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <h1 class="text-xl font-bold text-gray-800">Catat Pengeluaran</h1>
    </div>

    <div x-data="smartInputApp()" class="mb-6">

        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>

            <label class="block text-sm font-medium mb-2 text-blue-100 flex items-center gap-2">
                ✨ Smart Input (AI Powered)
            </label>

            <div class="relative">
                <input type="text"
                    x-model="prompt"
                    @keydown.enter="processAi()"
                    placeholder="Contoh: Nasi goreng 15rb pakai dana harian..."
                    class="w-full pl-4 pr-12 py-3.5 rounded-xl text-gray-800 placeholder-gray-400 bg-white/95 backdrop-blur-sm border-0 focus:ring-4 focus:ring-blue-400/30 transition shadow-inner"
                    :disabled="isLoading">

                <button @click="processAi()"
                    class="absolute right-2 top-2 p-1.5 rounded-lg bg-blue-100 text-blue-700 hover:bg-white hover:text-blue-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="isLoading || !prompt">

                    <span x-show="!isLoading" class="text-xl">🤖</span>

                    <svg x-show="isLoading" class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

            <p class="text-xs text-blue-200 mt-2 ml-1 opacity-80">
                *Cukup ketik kalimat biasa, kami yang isikan form-nya.
            </p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 relative">

        <form action="{{ route('transactions.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nominal (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-gray-400 font-bold">Rp</span>
                    <input type="number" name="amount" required placeholder="0"
                        id="input-amount"
                        class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition text-lg font-bold text-gray-800">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori</label>
                    <select name="category_id" id="input-category" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition appearance-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sumber Uang</label>
                    <select name="pocket_id" id="input-pocket" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition appearance-none">
                        @foreach($pockets as $pocket)
                            <option value="{{ $pocket->id }}" {{ $pocket->type == 'main' ? 'selected' : '' }}>
                                {{ $pocket->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                    class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition text-gray-600">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan</label>
                <textarea name="description" rows="2" placeholder="Beli apa tadi?" id="input-desc"
                    class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/30 transition transform active:scale-[0.98]">
                Simpan Pengeluaran
            </button>
        </form>
    </div>

    <script>
        /**
         * Smart Input Form Controller
         * Menggunakan Alpine.js untuk reactive form dengan AI integration
         */
        function smartInputApp() {
            return {
                prompt: '',
                isLoading: false,

                /**
                 * Process natural language input dengan AI
                 * Kirim ke /catat-jajan/smart endpoint
                 */
                async processAi() {
                    if (!this.prompt.trim()) return;

                    this.isLoading = true;

                    try {
                        const response = await fetch("{{ route('transactions.smart') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ text: this.prompt })
                        });

                        const data = await response.json();

                        // Auto-fill form fields dari AI response
                        if (data.amount > 0) {
                            document.getElementById('input-amount').value = data.amount;
                            this.flashElement('input-amount');
                        }

                        if (data.description) {
                            document.getElementById('input-desc').value = data.description;
                        }

                        if (data.category_id) {
                            document.getElementById('input-category').value = data.category_id;
                        }

                        if (data.pocket_id) {
                            let pocketSelect = document.getElementById('input-pocket');
                            if (pocketSelect.querySelector(`option[value="${data.pocket_id}"]`)) {
                                pocketSelect.value = data.pocket_id;
                                this.flashElement('input-pocket');
                            }
                        }

                        // Reset input
                        this.prompt = '';

                    } catch (error) {
                        console.error('Error:', error);
                        alert('Gagal memproses AI. Coba input manual ya.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                /**
                 * Visual feedback: flash element dengan highlight color
                 */
                flashElement(elementId) {
                    const el = document.getElementById(elementId);
                    if(el) {
                        el.classList.add('bg-yellow-50', 'text-blue-700');
                        setTimeout(() => {
                            el.classList.remove('bg-yellow-50', 'text-blue-700');
                        }, 800);
                    }
                }
            }
        }
    </script>

@endsection
