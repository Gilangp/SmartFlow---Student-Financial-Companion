<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SmartFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-xl border border-slate-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Buat Akun Baru 🚀</h1>
            <p class="text-slate-500">Mulai atur keuanganmu hari ini.</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                    placeholder="Jhon Doe" value="{{ old('name') }}">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                    placeholder="nama@email.com" value="{{ old('email') }}">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                    placeholder="••••••••">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Ulangi Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-200 mt-2">
                Daftar & Masuk
            </button>
        </form>

        <p class="text-center text-slate-500 mt-8 text-sm">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Masuk aja</a>
        </p>
    </div>

</body>
</html>
