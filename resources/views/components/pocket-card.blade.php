{{-- Reusable Pocket Card Component --}}
{{-- Props: $pocket, $icon, $color --}}

<div class="bg-white p-4 rounded-xl border border-gray-200 flex justify-between items-center shadow-sm">

    {{-- Left: Icon & Info --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-{{ $color }}-100 rounded-full flex items-center justify-center text-xl">
            {{ $icon }}
        </div>
        <div>
            <p class="font-semibold text-gray-700">{{ $pocket->name }}</p>
            <p class="text-xs text-gray-500">
                Target: Rp {{ number_format($pocket->target_amount, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Right: Balance & Progress Bar --}}
    <div class="text-right">
        <p class="font-bold text-gray-800">Rp {{ number_format($pocket->balance, 0, ',', '.') }}</p>

        {{-- Calculate Progress Percentage --}}
        @php
            $percent = 0;
            if($pocket->target_amount > 0) {
                $percent = ($pocket->balance / $pocket->target_amount) * 100;
                if($percent > 100) $percent = 100;
            }
        @endphp

        {{-- Progress Bar --}}
        <div class="w-20 h-1.5 bg-gray-100 rounded-full mt-1 ml-auto">
            <div class="h-1.5 bg-{{ $color }}-500 rounded-full transition-all duration-500"
                 style="width: {{ $percent }}%"></div>
        </div>
    </div>
</div>
