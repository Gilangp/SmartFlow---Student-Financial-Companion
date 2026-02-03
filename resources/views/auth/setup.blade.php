<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Awal - SmartFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-indigo-50 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white w-full max-w-2xl p-8 rounded-2xl shadow-xl border border-indigo-100 my-10">
        <div class="text-center mb-8">
            <span class="text-4xl">💰</span>
            <h1 class="text-2xl font-extrabold text-slate-900 mt-4 mb-2">Atur Aset Keuanganmu</h1>
            <p class="text-slate-500">Isi saldo awal untuk semua kantongmu biar AI tahu seberapa "Sultan" kamu.</p>
        </div>

        <form action="{{ route('setup.store') }}" method="POST" class="space-y-8">
            @csrf

            <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100">
                <h3 class="text-lg font-bold text-indigo-900 mb-4 flex items-center gap-2">
                    <span>🗓️</span> Siklus Gaji
                </h3>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Gajian (Reset Saldo)</label>
                    <select name="salary_date" class="w-full px-4 py-3 rounded-xl border border-indigo-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition bg-white">
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">Setiap Tanggal {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span>💵</span> Dompet Utama (Uang Jajan)
                </h3>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                    <input type="number" name="main_balance" required
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition font-mono text-lg"
                        placeholder="0">
                </div>
                <p class="text-xs text-slate-400 mt-1">Uang yang siap dipakai sehari-hari (Cash/ATM/E-Wallet).</p>
            </div>

            <hr class="border-slate-100">

            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span>🏦</span> Tabungan & Aset (Tidak Diganggu)
                </h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Tabungan / Investasi</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                            <input type="number" name="savings_balance" value="0"
                                class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Dana Darurat</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                            <input type="number" name="emergency_balance" value="0"
                                class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-indigo-200 text-lg">
                Simpan & Masuk Dashboard 🚀
            </button>
        </form>
    </div>

</body>
</html>
