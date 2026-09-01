@props(['package'])

@php
    $remaining = max(0, $package->daily_capacity - (int) ($package->today_booked_guests ?? 0));
    // ponytail: flat "<=5" threshold to keep the badge meaningful instead of
    // cluttering every card; revisit as capacity-relative (%) if packages
    // end up varying widely in daily_capacity.
    $showQuotaBadge = $remaining <= 5;
@endphp

<a href="{{ route('packages.show', $package->slug) }}" {{ $attributes->merge(['class' => 'group relative flex flex-col aspect-[4/3] rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-500']) }}>
    {{-- Image (full-bleed) --}}
    @if($package->images->first())
        <img src="{{ Storage::url($package->images->first()->image_path) }}" alt="{{ $package->name }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.06]" loading="lazy" decoding="async">
    @else
        <div class="absolute inset-0 bg-primary-50 flex items-center justify-center">
            <svg class="w-10 h-10 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
    @endif

    {{-- Gradient scrim --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-transparent"></div>

    {{-- Quota badge — only shown when slots are genuinely scarce today --}}
    @if($showQuotaBadge)
        <span class="absolute top-4 left-4 rounded-full px-3 py-1 text-xs font-bold shadow-sm {{ $remaining === 0 ? 'bg-red-500/90 text-white' : 'bg-amber-400/95 text-amber-950' }}">
            {{ $remaining === 0 ? __('Penuh hari ini') : __(':n slot tersisa hari ini', ['n' => $remaining]) }}
        </span>
    @endif

    {{-- Price badge --}}
    <span class="absolute top-4 right-4 rounded-full bg-white/90 backdrop-blur px-3 py-1 text-xs font-bold text-primary shadow-sm">
        {{ \App\Support\Currency::format($package->price) }}
    </span>

    {{-- Caption --}}
    <div class="relative mt-auto p-6 text-white">
        <div class="flex items-center gap-4 text-xs text-white/80 mb-2">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $package->duration_hours }} {{ __('jam') }}
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('Maks.') }} {{ $package->daily_capacity }}
            </span>
            @if(($package->reviews_count ?? 0) > 0)
            <span class="flex items-center gap-1.5">
                <span class="text-amber-400">★</span>
                {{ number_format($package->average_rating, 1) }}
            </span>
            @endif
        </div>

        <h3 class="text-lg font-bold mb-1.5 leading-tight">{{ $package->name }}</h3>

        <span class="text-sm font-semibold text-white/90 inline-flex items-center gap-1 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300">
            {{ __('Lihat Detail') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </span>
    </div>
</a>
