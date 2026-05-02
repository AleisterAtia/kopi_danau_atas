@extends('layouts.app')

@section('title', __('Beranda'))

@section('content')

{{-- ═══════════════════════════════════════
     SECTION 1 — Hero Banner
     ═══════════════════════════════════════ --}}
<section class="hero-section" @if($hero && $hero->images->first()) style="background: url('{{ Storage::url($hero->images->first()->image_path) }}') center/cover no-repeat;" @endif>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full hero-content py-32 lg:py-40">
        <div class="max-w-2xl">
            <p class="text-white/70 text-sm font-semibold tracking-widest uppercase mb-5 anim-fade-up" style="animation-delay:.1s">
                Agrowisata &bull; Edukasi &bull; Kopi Arabika
            </p>
            <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-extrabold text-white leading-[1.15] mb-6 anim-fade-up" style="animation-delay:.2s">
                {{ $hero->title ?? 'Jelajahi Keindahan Agrowisata Kopi Solok' }}
            </h1>
            <p class="text-lg text-white/80 leading-relaxed mb-10 max-w-xl anim-fade-up" style="animation-delay:.3s">
                {{ $hero->description ?? 'Rasakan pengalaman unik memetik kopi langsung dari kebun Arabika di dataran tinggi Danau Diatas.' }}
            </p>
            <div class="flex flex-wrap gap-4 anim-fade-up" style="animation-delay:.4s">
                <a href="{{ $hero->extra_data['cta_url'] ?? route('packages.index') }}" class="btn btn-primary btn-lg !bg-white !text-primary !border-white hover:!bg-primary-50">
                    {{ $hero->extra_data['cta_text'] ?? __('Pesan Sekarang') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('blog.index') }}" class="btn btn-lg !border-white/40 !text-white hover:!bg-white/10">
                    {{ __('Pelajari Lebih Lanjut') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Scroll hint --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 text-white/50 animate-bounce">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    </div>
</section>


{{-- ═══════════════════════════════════════
     SECTION 2 — About Us
     ═══════════════════════════════════════ --}}
@if($about)
<section class="py-20 lg:py-28 bg-white overflow-hidden" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Image side --}}
            <div class="relative">
                <div class="grid grid-cols-12 grid-rows-6 gap-3 h-[420px]">
                    @if($about->images->count() >= 2)
                        <div class="col-span-7 row-span-6 rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($about->images[0]->image_path) }}" alt="" class="w-full h-full object-cover">
                        </div>
                        <div class="col-span-5 row-span-3 rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($about->images[1]->image_path) }}" alt="" class="w-full h-full object-cover">
                        </div>
                        @if($about->images->count() >= 3)
                        <div class="col-span-5 row-span-3 rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($about->images[2]->image_path) }}" alt="" class="w-full h-full object-cover">
                        </div>
                        @endif
                    @elseif($about->images->first())
                        <div class="col-span-12 row-span-6 rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($about->images->first()->image_path) }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="col-span-12 row-span-6 rounded-xl overflow-hidden bg-gradient-to-br from-primary/10 to-accent/10 flex items-center justify-center">
                            <svg class="w-16 h-16 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Text side --}}
            <div>
                <span class="section-label mb-4">{{ __('Tentang Kami') }}</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-text mt-3 mb-6">{{ $about->title }}</h2>
                <div class="prose-content text-base mb-8">
                    {!! $about->description !!}
                </div>
                <a href="{{ $about->extra_data['cta_url'] ?? route('packages.index') }}" class="btn btn-outline">
                    {{ $about->extra_data['cta_text'] ?? __('Lihat Paket Wisata') }}
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
<section class="py-20 lg:py-28 bg-[#F8F9FA]" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-12 gap-4">
            <div>
                <span class="section-label mb-3">{{ __('Paket Pilihan') }}</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-text mt-3">{{ __('Pengalaman Terbaik Untuk Anda') }}</h2>
            </div>
            <a href="{{ route('packages.index') }}" class="btn btn-outline shrink-0 self-start sm:self-auto">
                {{ __('Lihat Semua') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredPackages as $package)
            <a href="{{ route('packages.show', $package->slug) }}" class="card group flex flex-col h-full">
                {{-- Image --}}
                <div class="relative h-52 overflow-hidden">
                    @if($package->images->first())
                        <img src="{{ Storage::url($package->images->first()->image_path) }}" alt="{{ $package->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
                            <svg class="w-10 h-10 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="p-5 flex flex-col flex-grow">
                    <div class="flex items-center gap-3 text-xs text-text-secondary mb-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $package->duration_hours }} jam
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Maks. {{ $package->daily_capacity }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-text mb-1.5 group-hover:text-primary transition-colors">{{ $package->name }}</h3>
                    <p class="text-sm text-text-secondary line-clamp-2 mb-4 flex-grow">{!! strip_tags($package->description) !!}</p>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-lg font-bold text-primary">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                        <span class="text-sm font-semibold text-primary group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Detail
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════
     SECTION 4 — Education / Coffee Process
     ═══════════════════════════════════════ --}}
@if($education)
<section class="py-20 lg:py-28 bg-primary overflow-hidden" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Images --}}
            <div class="order-2 lg:order-1">
                @if($education->images->count() > 0)
                <div class="grid grid-cols-2 gap-4">
                    @foreach($education->images->take(4) as $index => $img)
                        <div class="rounded-xl overflow-hidden {{ $index % 2 == 1 ? 'mt-6' : '' }}">
                            <img src="{{ Storage::url($img->image_path) }}" alt="{{ $img->caption ?? '' }}" class="w-full aspect-[4/5] object-cover" loading="lazy">
                        </div>
                    @endforeach
                </div>
                @else
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-white/10 aspect-[4/5]"></div>
                    <div class="rounded-xl bg-white/10 aspect-[4/5] mt-6"></div>
                </div>
                @endif
            </div>

            {{-- Text --}}
            <div class="order-1 lg:order-2">
                <span class="section-label mb-4 !text-accent-warm before:!bg-accent-warm">{{ __('Edukasi') }}</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-white mt-3 mb-6">{{ $education->title }}</h2>
                <div class="prose-content text-white/75 text-base mb-8">
                    {!! $education->description !!}
                </div>

                @if(isset($education->extra_data) && is_array($education->extra_data))
                <ul class="space-y-3.5 mb-10">
                    @foreach($education->extra_data as $key => $value)
                        @if(str_starts_with($key, 'bullet_'))
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-accent-warm/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-accent-warm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-white/85 text-sm leading-relaxed">{{ $value }}</span>
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
<section class="py-20 lg:py-28 bg-white" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-10 lg:gap-16 items-start">
            {{-- Left heading --}}
            <div class="lg:col-span-2">
                <span class="section-label mb-4">{{ __('Testimoni') }}</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-text mt-3 mb-4">{{ $testimonials->title ?? __('Apa Kata Pengunjung?') }}</h2>
                <p class="text-text-secondary leading-relaxed mb-6">{{ $testimonials->description ?? __('Pengalaman berharga dari mereka yang telah berkunjung ke agrowisata kami.') }}</p>

                @if($approvedReviews->count() > 1)
                <div class="flex items-center gap-4">
                    <button onclick="prevTestimonial()" class="w-10 h-10 rounded-full border border-gray-200 text-text flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span id="testimonial-counter" class="text-sm text-text-muted font-medium tabular-nums">1 / {{ $approvedReviews->count() }}</span>
                    <button onclick="nextTestimonial()" class="w-10 h-10 rounded-full border border-gray-200 text-text flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                @endif
            </div>

            {{-- Right card --}}
            <div class="lg:col-span-3">
                <div id="testimonial-carousel" class="relative min-h-[260px]">
                    @foreach($approvedReviews as $index => $review)
                    <div class="testimonial-item absolute inset-0 transition-opacity duration-500" style="display:{{ $index === 0 ? 'block' : 'none' }};opacity:{{ $index === 0 ? '1' : '0' }};">
                        <div class="bg-[#F8F9FA] rounded-2xl p-8 lg:p-10">
                            {{-- Stars --}}
                            <div class="flex gap-0.5 mb-5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>

                            <blockquote class="text-lg text-text leading-relaxed mb-8">
                                "{{ $review->comment }}"
                            </blockquote>

                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ substr($review->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-text text-sm">{{ $review->user->name }}</p>
                                    <p class="text-text-muted text-xs">{{ $review->tourPackage->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════
     SECTION 6 — Location Map
     ═══════════════════════════════════════ --}}
<section class="bg-[#F8F9FA] py-20" data-reveal>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-10 items-center">
            <div class="lg:col-span-2">
                <span class="section-label mb-4">{{ __('Lokasi') }}</span>
                <h2 class="text-3xl font-extrabold text-text mt-3 mb-4">{{ __('Kunjungi Kami') }}</h2>
                <p class="text-text-secondary text-sm leading-relaxed mb-6">{{ $settings['company_address'] ?? 'Alahan Panjang, Solok, Sumatera Barat' }}</p>

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
                <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-200 h-[380px]">
                    @if(!empty($settings['google_maps_embed']))
                        <iframe src="{{ $settings['google_maps_embed'] }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    @else
                        <div class="w-full h-full bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm">Google Maps</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
