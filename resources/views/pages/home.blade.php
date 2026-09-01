@extends('layouts.app')

@section('title', __('Beranda'))

@push('head')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

<div x-data="{ videoOpen: false, videoSrc: '' }">

{{-- ═══════════════════════════════════════
     SECTION 1 — Hero Banner
     ═══════════════════════════════════════ --}}
<section class="hero-section" @if($hero && $hero->images->first()) style="background: url('{{ Storage::url($hero->images->first()->image_path) }}') center/cover no-repeat;" @endif>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full hero-content py-32 lg:py-40">
        <div class="max-w-2xl">
            <p class="hero-eyebrow anim-fade-up" style="animation-delay:.1s">
                {{ __('Agrowisata') }} <span>&bull;</span> {{ __('Edukasi') }} <span>&bull;</span> {{ __('Kopi Arabika') }}
            </p>
            <h1 class="text-4xl sm:text-5xl lg:text-[3.75rem] font-extrabold text-white leading-[1.08] tracking-[-0.02em] mb-6 anim-fade-up" style="animation-delay:.2s">
                {{ $hero->title ?? __('Jelajahi Keindahan  & Autentik Agrowisata Kopi Solok') }}
            </h1>
            <div class="text-lg text-white/80 leading-relaxed mb-10 max-w-xl anim-fade-up" style="animation-delay:.3s">
                {!! $hero->description ?? __('Rasakan pengalaman unik memetik kopi langsung dari kebun Arabika di dataran tinggi Danau Diatas.') !!}
            </div>
            <div class="flex flex-wrap gap-3 anim-fade-up" style="animation-delay:.4s">
                <a href="{{ $hero->extra_data['cta_url'] ?? route('packages.index') }}" class="btn btn-primary btn-lg !bg-white !text-primary !border-white hover:!bg-primary-50">
                    {{ __($hero->extra_data['cta_text'] ?? 'Pesan Sekarang') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <!-- <a href="{{ route('blog.index') }}" class="btn btn-lg !border-white/35 !text-white hover:!bg-white/10">
                    {{ __('Pelajari Lebih Lanjut') }}
                </a> -->
                @if($hero && $hero->video_path)
                <button type="button" @click="videoSrc='{{ Storage::url($hero->video_path) }}'; videoOpen=true" class="btn btn-lg !border-white/35 !text-white hover:!bg-white/10">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    {{ __('Lihat Video') }}
                </button>
                @endif
            </div>

            {{-- Mobile-only quick glimpse — avatar stack under the CTAs.
                 The floating spotlight card below needs room the photo
                 doesn't have on a phone, so mobile keeps this compact
                 alternative instead of losing the package glimpse entirely. --}}
            @if($featuredPackages->count() > 0)
            <div class="flex lg:hidden items-center gap-4 mt-8 anim-fade-up" style="animation-delay:.5s">
                <div class="flex -space-x-3">
                    @foreach($featuredPackages->take(4) as $package)
                    <a href="{{ route('packages.show', $package->slug) }}" class="relative w-11 h-11 rounded-full ring-2 ring-white/60 overflow-hidden shrink-0" title="{{ $package->name }}">
                        @if($package->images->first())
                            <img src="{{ Storage::url($package->images->first()->image_path) }}" alt="{{ $package->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        @else
                            <div class="w-full h-full bg-primary-700"></div>
                        @endif
                    </a>
                    @endforeach
                </div>
                <a href="{{ route('packages.index') }}" class="text-sm text-white/85">
                    <span class="font-semibold text-white">{{ $featuredPackages->count() }}+</span> {{ __('paket wisata siap dijelajahi') }}
                </a>
            </div>
            @endif
        </div>

        {{-- Floating package spotlight — glass card parked on the open
             right side of the photo (the text column already claims the
             left/darkened side). Only ~320px wide and glassy, so most of
             the lake/mountain stays visible; auto-rotates through
             featured packages using the same rotator pattern as the
             mobile hero-facts strip below. Desktop only — no room on
             mobile without covering the shot entirely. --}}
        @if($featuredPackages->count() > 0)
        <div class="hidden lg:block absolute right-0 xl:right-8 top-1/2 -translate-y-1/2 w-[300px] anim-fade-up" style="animation-delay:.5s"
            x-data="{
                items: @js($featuredPackages->take(4)->map(fn ($p) => [
                    'name' => $p->name,
                    'price' => \App\Support\Currency::format($p->price),
                    'image' => optional($p->images->first())->image_path ? Storage::url($p->images->first()->image_path) : null,
                    'url' => route('packages.show', $p->slug),
                ])),
                active: 0,
            }"
            x-init="items.length > 1 && setInterval(() => active = (active + 1) % items.length, 4000)"
        >
            <div class="bg-white/10 backdrop-blur-md border border-white/25 rounded-2xl shadow-2xl p-4">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-white/70 mb-3">{{ __('Paket Pilihan') }}</p>

                <div class="relative aspect-[4/3] rounded-xl overflow-hidden mb-3 bg-primary-800">
                    <template x-for="(item, i) in items" :key="i">
                        <a :href="item.url" x-show="active === i"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0 group">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>
                    </template>
                </div>

                <template x-for="(item, i) in items" :key="'label-' + i">
                    <a :href="item.url" x-show="active === i" class="block group">
                        <p class="text-white font-bold text-base leading-tight mb-1" x-text="item.name"></p>
                        <div class="flex items-center justify-between">
                            <span class="text-white/80 text-sm" x-text="item.price"></span>
                            <span class="text-white text-sm font-semibold inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                {{ __('Lihat') }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </a>
                </template>

                <div class="flex gap-1.5 mt-4" x-show="items.length > 1">
                    <template x-for="(item, i) in items" :key="'dot-' + i">
                        <button type="button" @click="active = i" class="h-1.5 rounded-full transition-all duration-300" :class="active === i ? 'w-6 bg-white' : 'w-1.5 bg-white/40'"></button>
                    </template>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Signature: editorial facts strip anchored to hero base.
         sm+ shows all three side by side; on mobile there isn't room for
         that without wrapping into clutter, so it rotates one fact at a
         time instead (also doubles as the page's "changing text" moment). --}}
    <div class="hero-facts anim-fade-up" style="animation-delay:.55s">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <dl class="hidden sm:flex flex-wrap items-center gap-x-10 gap-y-3 py-5">
                <div class="hero-fact">
                    <dt>{{ __('Ketinggian') }}</dt>
                    <dd>{{ __('Dataran Tinggi Danau Diatas') }}</dd>
                </div>
                <span class="hero-fact-sep"></span>
                <div class="hero-fact">
                    <dt>{{ __('Varietas') }}</dt>
                    <dd>{{ __('100% Kopi Arabika') }}</dd>
                </div>
                <span class="hero-fact-sep"></span>
                <div class="hero-fact">
                    <dt>{{ __('Lokasi') }}</dt>
                    <dd>{{ __('Danau Diatas, Kabupaten Solok') }}</dd>
                </div>
            </dl>

            <div class="sm:hidden py-5"
                x-data="{
                    facts: [
                        { label: @js(__('Ketinggian')), value: @js(__('Dataran Tinggi Danau Diatas')) },
                        { label: @js(__('Varietas')), value: @js(__('100% Kopi Arabika')) },
                        { label: @js(__('Lokasi')), value: @js(__('Danau Diatas, Kabupaten Solok')) },
                    ],
                    active: 0,
                }"
                x-init="setInterval(() => active = (active + 1) % facts.length, 3000)"
            >
                <div class="hero-fact-rotator">
                    <template x-for="(fact, i) in facts" :key="i">
                        <div class="hero-fact" x-show="active === i"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-end="opacity-0">
                            <dt x-text="fact.label"></dt>
                            <dd x-text="fact.value"></dd>
                        </div>
                    </template>
                </div>
                <div class="hero-fact-dots">
                    <template x-for="(fact, i) in facts" :key="'dot-' + i">
                        <span class="hero-fact-dot" :class="{ 'is-active': active === i }"></span>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════
     SECTION 2 — About Us
     ═══════════════════════════════════════ --}}
@if($about)
<section class="py-24 lg:py-32 bg-white overflow-hidden" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Image side --}}
            <div class="relative">
                <div class="grid grid-cols-12 grid-rows-6 gap-4 h-[440px]">
                    @if($about->images->count() >= 2)
                        <div class="col-span-7 row-span-6 rounded-lg overflow-hidden">
                            <img src="{{ Storage::url($about->images[0]->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                        <div class="col-span-5 row-span-3 rounded-lg overflow-hidden">
                            <img src="{{ Storage::url($about->images[1]->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                        @if($about->images->count() >= 3)
                        <div class="col-span-5 row-span-3 rounded-lg overflow-hidden">
                            <img src="{{ Storage::url($about->images[2]->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                        @endif
                    @elseif($about->images->first())
                        <div class="col-span-12 row-span-6 rounded-lg overflow-hidden">
                            <img src="{{ Storage::url($about->images->first()->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        </div>
                    @else
                        <div class="col-span-12 row-span-6 rounded-lg overflow-hidden bg-primary-50 flex items-center justify-center">
                            <svg class="w-16 h-16 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                @if($about->video_path)
                <button type="button" @click="videoSrc='{{ Storage::url($about->video_path) }}'; videoOpen=true" class="absolute inset-0 m-auto w-16 h-16 rounded-full bg-white/90 hover:bg-white flex items-center justify-center shadow-lg transition-colors" aria-label="{{ __('Lihat Video') }}">
                    <svg class="w-6 h-6 text-primary ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </button>
                @endif
            </div>

            {{-- Text side --}}
            <div>
                <span class="section-label mb-5">{{ __('Tentang Kami') }}</span>
                <h2 class="text-3xl lg:text-[2.5rem] font-extrabold text-text tracking-[-0.02em] leading-tight mt-3 mb-6">{{ $about->title }}</h2>
                <div class="prose-content text-base mb-8">
                    {!! $about->description !!}
                </div>
                <a href="{{ route('about') }}" class="btn btn-outline">
                    {{ __($about->extra_data['cta_text'] ?? 'Lihat Selengkapnya') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════
     SECTION 3 — Featured Tour Packages
     ═══════════════════════════════════════ --}}
@if($featuredPackages->count() > 0)
<section class="py-24 lg:py-32 bg-bg-warm" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-14 gap-4">
            <div>
                <span class="section-label mb-3">{{ __('Paket Pilihan') }}</span>
                <h2 class="text-3xl lg:text-[2.5rem] font-extrabold text-text tracking-[-0.02em] mt-3">{{ __('Pengalaman Terbaik Untuk Anda') }}</h2>
            </div>
            <a href="{{ route('packages.index') }}" class="btn btn-outline shrink-0 self-start sm:self-auto">
                {{ __('Lihat Semua') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredPackages as $package)
                <x-package-card :package="$package" />
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════
     SECTION 4 — Education / Coffee Process
     ═══════════════════════════════════════ --}}
@if($education)
<section class="py-24 lg:py-32 bg-primary overflow-hidden" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Images --}}
            <div class="order-2 lg:order-1">
                @if($education->images->count() > 0)
                <div class="grid grid-cols-2 gap-4">
                    @foreach($education->images->take(4) as $index => $img)
                        <div class="rounded-lg overflow-hidden {{ $index % 2 == 1 ? 'mt-8' : '' }}">
                            <img src="{{ Storage::url($img->image_path) }}" alt="{{ $img->caption ?? '' }}" class="w-full aspect-[4/5] object-cover" loading="lazy" decoding="async">
                        </div>
                    @endforeach
                </div>
                @else
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-lg bg-white/10 aspect-[4/5]"></div>
                    <div class="rounded-lg bg-white/10 aspect-[4/5] mt-8"></div>
                </div>
                @endif
            </div>

            {{-- Text --}}
            <div class="order-1 lg:order-2">
                <span class="section-label mb-5 !text-accent-warm before:!bg-accent-warm">{{ __('Edukasi') }}</span>
                <h2 class="text-3xl lg:text-[2.5rem] font-extrabold text-white tracking-[-0.02em] leading-tight mt-3 mb-6">{{ $education->title }}</h2>
                <div class="prose-content !text-white/70 text-base mb-8">
                    {!! $education->description !!}
                </div>

                @if(isset($education->extra_data) && is_array($education->extra_data))
                <ul class="space-y-4 mb-10">
                    @foreach($education->extra_data as $key => $value)
                        @if(str_starts_with($key, 'bullet_'))
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-accent-warm/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-accent-warm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-white/85 text-sm leading-relaxed">{{ __($value) }}</span>
                        </li>
                        @endif
                    @endforeach
                </ul>
                @endif

                <a href="{{ route('blog.index') }}" class="btn btn-lg !bg-accent !border-accent !text-white hover:!bg-accent-light">
                    {{ __('Baca Blog Edukasi') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════
     SECTION 5 — Testimonials
     ═══════════════════════════════════════ --}}
@if($approvedReviews->count() > 0 && $testimonials)
<section class="py-24 lg:py-32 bg-white" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-10 lg:gap-16 items-start">
            {{-- Left heading --}}
            <div class="lg:col-span-2">
                <span class="section-label mb-5">{{ __('Testimoni') }}</span>
                <h2 class="text-3xl lg:text-[2.5rem] font-extrabold text-text tracking-[-0.02em] leading-tight mt-3 mb-4">{{ $testimonials->title ?? __('Apa Kata Pengunjung?') }}</h2>
                <p class="text-text-secondary leading-relaxed mb-6">{{ $testimonials->description ?? __('Pengalaman berharga dari mereka yang telah berkunjung ke agrowisata kami.') }}</p>

                @if($approvedReviews->count() > 1)
                <div class="flex items-center gap-4">
                    <button onclick="prevTestimonial()" class="w-11 h-11 rounded-full border border-border text-text flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span id="testimonial-counter" class="text-sm text-text-muted font-medium tabular-nums">1 / {{ $approvedReviews->count() }}</span>
                    <button onclick="nextTestimonial()" class="w-11 h-11 rounded-full border border-border text-text flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                @endif
            </div>

            {{-- Right card --}}
            <div class="lg:col-span-3">
                <div id="testimonial-carousel" class="relative min-h-[260px]">
                    @foreach($approvedReviews as $index => $review)
                    <div class="testimonial-item absolute inset-0 transition-opacity duration-500" style="opacity:{{ $index === 0 ? '1' : '0' }};pointer-events:{{ $index === 0 ? 'auto' : 'none' }};">
                        <figure class="bg-bg-warm border border-border rounded-xl p-8 lg:p-10">
                            {{-- Stars --}}
                            <div class="flex gap-0.5 mb-5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>

                            <blockquote class="text-lg text-text leading-relaxed mb-8">
                                &ldquo;{{ $review->comment }}&rdquo;
                            </blockquote>

                            <figcaption class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ substr($review->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-text text-sm">{{ $review->user->name }}</p>
                                    <p class="text-text-muted text-xs">{{ $review->tourPackage->name }}</p>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════
     SECTION 5.5 — FAQ
     ═══════════════════════════════════════ --}}
@if($faqs->count() > 0)
<section id="faq" class="bg-primary py-24 lg:py-28" data-reveal>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="section-label justify-center mb-5 !text-accent-warm before:!bg-accent-warm">{{ __('Bantuan') }}</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-white tracking-[-0.02em] mt-3">{{ __('Pertanyaan yang Sering Diajukan') }}</h2>
        </div>

        <div x-data="{ open: 0 }" class="space-y-3">
            @foreach($faqs as $i => $faq)
                @php($isPembatalan = str_contains(strtolower($faq->question), 'batal') || str_contains(strtolower($faq->question), 'refund'))
                <div id="{{ $isPembatalan ? 'pembatalan' : '' }}" class="card">
                    <button type="button" @click="open = (open === {{ $i }} ? null : {{ $i }})"
                            class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left">
                        <span class="font-semibold text-text">{{ $faq->question }}</span>
                        <svg class="w-5 h-5 flex-shrink-0 text-text-secondary transition-transform"
                             :class="{ 'rotate-180': open === {{ $i }} }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="px-5 pb-4 prose-content">
                        {!! $faq->answer !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════
     SECTION 6 — Location Map
     ═══════════════════════════════════════ --}}
<section class="bg-white py-24 lg:py-28" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-10 items-center">
            <div class="lg:col-span-2">
                <span class="section-label mb-5">{{ __('Lokasi') }}</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-text tracking-[-0.02em] mt-3 mb-4">{{ __('Kunjungi Kami') }}</h2>
                <p class="text-text-secondary text-sm leading-relaxed mb-6">{{ $settings['company_address'] ?? 'Danau Diatas, Kabupaten Solok, Sumatera Barat' }}</p>

                <div class="space-y-3 mb-8 text-sm text-text-secondary">
                    @if(!empty($settings['company_phone']))
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $settings['company_phone'] }}
                    </div>
                    @endif
                    @if(!empty($settings['company_email']))
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $settings['company_email'] }}
                    </div>
                    @endif
                </div>

                <a href="https://maps.google.com/?q=CV+Kopi+Danau+Atas+Alahan+Panjang" target="_blank" class="btn btn-primary">
                    {{ __('Buka di Google Maps') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-xl overflow-hidden border border-border h-[380px]">
                    @if(!empty($settings['google_maps_embed']))
                        @if(str_starts_with(trim($settings['google_maps_embed']), '<iframe'))
                            <div class="w-full h-full [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0">
                                {!! $settings['google_maps_embed'] !!}
                            </div>
                        @else
                            <iframe src="{{ $settings['google_maps_embed'] }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @endif
                    @else
                        <div class="w-full h-full bg-primary-50 flex flex-col items-center justify-center text-text-muted">
                            <svg class="w-10 h-10 mb-2 text-primary/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm">Google Maps</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Video modal — shared by Hero & About "Lihat Video" triggers --}}
<div x-show="videoOpen" x-cloak x-transition.opacity @click="videoOpen=false; videoSrc=''" @keydown.escape.window="videoOpen=false; videoSrc=''" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4">
    <button type="button" @click.stop="videoOpen=false; videoSrc=''" class="absolute top-6 right-6 text-white/80 hover:text-white text-3xl leading-none">&times;</button>
    <video :src="videoSrc" x-show="videoOpen" controls autoplay class="max-w-4xl w-full rounded-lg" @click.stop></video>
</div>

</div>

@endsection
