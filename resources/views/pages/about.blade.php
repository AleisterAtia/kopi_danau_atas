@extends('layouts.app')

@section('title', __('Tentang Kami'))

@push('head')
    <style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════
     HERO band
     ═══════════════════════════════════════ --}}
<section class="relative bg-primary pt-32 pb-16 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary to-primary-light opacity-95"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- breadcrumb (light, for dark hero) --}}
        <nav aria-label="Breadcrumb" class="text-sm mb-5">
            <ol class="flex flex-wrap items-center gap-1.5 text-white/60">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Beranda') }}</a></li>
                <li class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-white font-medium" aria-current="page">{{ __('Tentang Kami') }}</span>
                </li>
            </ol>
        </nav>
        <span class="section-label !text-accent-warm before:!bg-accent-warm mb-4">{{ __('Tentang Kami') }}</span>
        <h1 class="text-4xl lg:text-5xl font-extrabold text-white font-heading mt-3 max-w-3xl">
            {{ $about->title ?? __('Kopi Arabika Solok, Langsung dari Kebun Kami') }}
        </h1>
    </div>
</section>

{{-- ═══════════════════════════════════════
     Company story
     ═══════════════════════════════════════ --}}
@if($about)
<section class="py-20 lg:py-28 bg-white" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Image side --}}
            <div class="relative">
                @if($about->images->count() >= 2)
                    <div class="grid grid-cols-12 grid-rows-6 gap-3 h-[420px]">
                        <div class="col-span-7 row-span-6 rounded-2xl overflow-hidden">
                            <img src="{{ Storage::url($about->images[0]->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                        <div class="col-span-5 row-span-3 rounded-2xl overflow-hidden">
                            <img src="{{ Storage::url($about->images[1]->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                        @if($about->images->count() >= 3)
                        <div class="col-span-5 row-span-3 rounded-2xl overflow-hidden">
                            <img src="{{ Storage::url($about->images[2]->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                        @endif
                    </div>
                @elseif($about->images->first())
                    <div class="rounded-2xl overflow-hidden h-[420px]">
                        <img src="{{ Storage::url($about->images->first()->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                    </div>
                @else
                    <div class="rounded-2xl overflow-hidden h-[420px] bg-gradient-to-br from-primary/10 to-accent/10 flex items-center justify-center">
                        <svg class="w-20 h-20 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                    </div>
                @endif
            </div>

            {{-- Text side --}}
            <div>
                <span class="section-label mb-4">{{ __('Cerita Kami') }}</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-text font-heading mt-3 mb-6">{{ $about->title }}</h2>
                <div class="prose-content text-base">
                    {!! $about->description !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════
     Interactive coffee varieties explorer
     ═══════════════════════════════════════ --}}
@if($varieties->isNotEmpty())
<section class="py-20 lg:py-28 bg-[#F8F9FA]" data-reveal x-data="{ active: 0 }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="section-label justify-center mb-4">{{ __('Kebun Kami') }}</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-text font-heading mt-3 mb-4">{{ __('Varietas Kopi Kami') }}</h2>
            <p class="text-text-secondary">{{ __('Jelajahi ragam Arabika unggulan yang kami tanam dan olah langsung di dataran tinggi Danau Diatas.') }}</p>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap justify-center gap-3 mb-10">
            @foreach($varieties as $variety)
                <button type="button"
                    @click="active = {{ $loop->index }}"
                    :class="active === {{ $loop->index }} ? 'bg-primary text-white shadow-md' : 'bg-white text-text-secondary border border-border hover:border-primary hover:text-primary'"
                    class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all">
                    {{ $variety->name }}
                </button>
            @endforeach
        </div>

        {{-- Panels --}}
        <div class="relative">
            @foreach($varieties as $variety)
                <div x-show="active === {{ $loop->index }}" x-cloak
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="card grid md:grid-cols-2 gap-0 overflow-hidden">
                        {{-- Image --}}
                        <div class="aspect-[4/3] md:aspect-auto md:min-h-[380px]">
                            @if($variety->image_path)
                                <img src="{{ Storage::url($variety->image_path) }}" alt="{{ $variety->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-primary/10 to-accent/10 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                                </div>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="p-8 lg:p-10 flex flex-col justify-center">
                            <h3 class="text-2xl lg:text-3xl font-extrabold text-text font-heading">{{ $variety->name }}</h3>
                            @if($variety->origin)
                                <div class="mt-3 inline-flex items-center gap-1.5 text-sm text-accent font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ __('Asal') }}: {{ $variety->origin }}
                                </div>
                            @endif
                            @if($variety->description)
                                <div class="prose-content text-sm mt-4">{!! $variety->description !!}</div>
                            @endif
                            @if($variety->flavor_profile)
                                <div class="mt-6">
                                    <p class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2">{{ __('Profil Rasa') }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(array_filter(array_map('trim', explode(',', $variety->flavor_profile))) as $note)
                                            <span class="px-3 py-1 rounded-full bg-primary-50 text-primary text-xs font-medium">{{ $note }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════
     CTA
     ═══════════════════════════════════════ --}}
<section class="py-20 bg-primary" data-reveal>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl lg:text-4xl font-extrabold text-white font-heading mb-4">{{ __('Siap menjelajahi kebun kami?') }}</h2>
        <p class="text-white/70 mb-8">{{ __('Ikuti paket agrowisata kami dan rasakan langsung proses kopi dari kebun ke cangkir.') }}</p>
        <a href="{{ route('packages.index') }}" class="btn btn-primary btn-lg !bg-white !text-primary">
            {{ __('Lihat Paket Wisata') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

@endsection
