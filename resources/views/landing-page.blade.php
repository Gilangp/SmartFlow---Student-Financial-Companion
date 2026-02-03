<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFlow - Keuangan Anti Boncos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="max-w-5xl mx-auto p-6 flex justify-between items-center">
        <div class="flex items-center gap-2 font-extrabold text-2xl text-indigo-600">
            <span>💸</span> SmartFlow
        </div>
        <div class="space-x-4">
            <a href="{{ route('login') }}" class="font-semibold hover:text-indigo-600 transition">Masuk</a>
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-5 py-2.5 rounded-full font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">Daftar Sekarang</a>
        </div>
    </nav>

    <header class="max-w-3xl mx-auto text-center mt-20 px-6">
        <div class="inline-block bg-indigo-100 text-indigo-700 px-4 py-1.5 rounded-full text-sm font-bold mb-6">
            ✨ Dilengkapi AI Super Julid
        </div>
        <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 leading-tight mb-6">
            Atur Duit Gak Pake <span class="text-indigo-600">Pusing.</span>
        </h1>
        <p class="text-lg text-slate-500 mb-10 leading-relaxed">
            Catat pengeluaran cuma modal curhat. Biarkan AI kami yang menganalisa, mencatat,
            dan <span class="text-red-500 font-bold">memarahimu</span> kalau boros.
        </p>
        <div class="flex justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-indigo-700 transition shadow-xl shadow-indigo-200 hover:-translate-y-1">
                Mulai Gratis 🚀
            </a>
        </div>
    </header>

    <section class="max-w-5xl mx-auto mt-24 grid md:grid-cols-3 gap-8 px-6 pb-20">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            <div class="text-4xl mb-4">🤖</div>
            <h3 class="text-xl font-bold mb-2">AI Financial Roaster</h3>
            <p class="text-slate-500">Asisten yang siap menyindir kalau kamu kebanyakan jajan kopi padahal gaji masih lama.</p>
        </div>
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            <div class="text-4xl mb-4">✍️</div>
            <h3 class="text-xl font-bold mb-2">Smart Input</h3>
            <p class="text-slate-500">Gak perlu input manual. Ketik "Beli Nasi Padang 20rb", otomatis tercatat rapi.</p>
        </div>
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            <div class="text-4xl mb-4">🎯</div>
            <h3 class="text-xl font-bold mb-2">Budget Control</h3>
            <p class="text-slate-500">Pantau sisa jatah harian secara real-time biar gak makan promag di akhir bulan.</p>
        </div>
    </section>

</body>
</html>
