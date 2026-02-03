{{-- Navigation Bar - Sticky di atas --}}
<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-md mx-auto px-4 py-4 flex justify-between items-center">

        {{-- Logo & Brand --}}
        <h1 class="text-xl font-bold text-blue-600 flex items-center gap-2">
            SmartFlow 💸
        </h1>

        {{-- User Info & Logout --}}
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ auth()->user()->name ?? 'Tamu' }}</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs text-red-500 hover:underline font-semibold">
                    Keluar
                </button>
            </form>
        </div>

    </div>
</nav>
