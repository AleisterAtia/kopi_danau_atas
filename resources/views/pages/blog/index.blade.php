@extends('layouts.app')

@section('title', __('Blog Edukasi Kopi'))

@push('head')
    @include('partials.navbar-light-override')
@endpush

@section('content')
@php
    // Helper kecil untuk estimasi waktu baca (200 kata/menit)
    $readTime = function ($content) {
        $words = str_word_count(strip_tags((string) $content));
        return max(1, (int) ceil($words / 200));
    };
@endphp

<div class="pt-28 pb-16 bg-bg-warm min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="mb-6">
            @include('partials.breadcrumbs', ['items' => [
                ['label' => __('Beranda'), 'url' => route('home')],
                ['label' => __('Blog')],
            ]])
        </div>

        {{-- Page header --}}
        <div class="mb-10">
            <h1 class="text-4xl md:text-5xl font-extrabold text-text font-heading tracking-tight">
                {{ __('Jurnal Kopi') }}
            </h1>
            <p class="mt-3 text-text-secondary max-w-2xl">
                {{ __('Temukan wawasan mendalam tentang budidaya kopi, teknik penyeduhan, dan pesona alam Alahan Panjang.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- ── Main column ─────────────────────────── --}}
            <div class="lg:col-span-2 space-y-10">

                {{-- Featured post --}}
                @if($featured && !$search && !$categorySlug)
                <a href="{{ route('blog.show', $featured->slug) }}"
                   class="relative block rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 group aspect-[16/10]">
                    @if($featured->thumbnail)
                        <img src="{{ Storage::url($featured->thumbnail) }}" alt="{{ $featured->title }}"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <div class="absolute inset-0 bg-primary-light"></div>
                    @endif

                    {{-- Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent"></div>

                    <div class="relative h-full flex flex-col justify-end p-6 md:p-10 text-white">
                        @if($featured->category)
                            <span class="inline-flex self-start bg-primary text-white text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-md mb-4">
                                {{ $featured->category->name }}
                            </span>
                        @endif

                        <h2 class="font-heading font-bold text-2xl md:text-4xl leading-tight max-w-3xl">
                            {{ $featured->title }}
                        </h2>

                        <p class="mt-3 text-white/80 max-w-2xl line-clamp-2 text-sm md:text-base">
                            {{ \Illuminate\Support\Str::limit(strip_tags($featured->content), 160) }}
                        </p>

                        <div class="mt-5 flex items-center gap-4 text-xs md:text-sm text-white/80">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $featured->published_at->translatedFormat('d F Y') }}
                            </span>
                            <span class="w-1 h-1 rounded-full bg-white/50"></span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $readTime($featured->content) }} {{ __('Menit Baca') }}
                            </span>
                        </div>
                    </div>
                </a>
                @endif

                {{-- Category filter pills --}}
                @if($categories->count() > 0)
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('blog.index', request()->only('search')) }}"
                       class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                              {{ !$categorySlug ? 'bg-primary text-white' : 'bg-white text-text-secondary hover:bg-bg-warm border border-border' }}">
                        {{ __('Semua') }}
                    </a>

                    @foreach($categories as $category)
                        @if($category->posts_count > 0)
                            <a href="{{ route('blog.index', array_merge(request()->only('search'), ['category' => $category->slug])) }}"
                               class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                                      {{ $categorySlug === $category->slug ? 'bg-primary text-white' : 'bg-white text-text-secondary hover:bg-bg-warm border border-border' }}">
                                {{ $category->name }}
                            </a>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Filter info --}}
                @if($search || $categorySlug)
                    <div class="text-sm text-text-secondary">
                        @if($search)
                            {{ __('Hasil pencarian untuk') }}: <span class="font-semibold text-text">"{{ $search }}"</span>
                        @endif
                        @if($categorySlug)
                            <span class="ml-1">{{ __('dalam kategori') }}
                                <span class="font-semibold text-text">{{ optional($categories->firstWhere('slug', $categorySlug))->name }}</span>
                            </span>
                        @endif
                        <a href="{{ route('blog.index') }}" class="ml-2 text-primary hover:underline">{{ __('Hapus filter') }}</a>
                    </div>
                @endif

                {{-- Posts grid --}}
                @if($posts->isEmpty())
                    <div class="text-center py-16 bg-white rounded-xl border border-border">
                        <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 8M6 12h12M6 16h12M6 8h12" />
                        </svg>
                        <h3 class="mt-3 text-base font-semibold text-text">{{ __('Belum ada artikel') }}</h3>
                        <p class="mt-1 text-sm text-text-secondary">{{ __('Coba ubah kata kunci atau filter Anda.') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($posts as $post)
                        <article class="card overflow-hidden flex flex-col">
                            <a href="{{ route('blog.show', $post->slug) }}" class="relative aspect-[16/10] overflow-hidden block group">
                                @if($post->thumbnail)
                                    <img src="{{ Storage::url($post->thumbnail) }}" alt="{{ $post->title }}"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full bg-primary-light flex items-center justify-center">
                                        <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </a>

                            <div class="p-5 flex flex-col flex-grow">
                                <div class="flex items-center gap-2 text-xs mb-3">
                                    @if($post->category)
                                        <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                                           class="text-primary font-semibold hover:underline">
                                            {{ $post->category->name }}
                                        </a>
                                        <span class="w-1 h-1 rounded-full bg-text-muted"></span>
                                    @endif
                                    <span class="text-text-secondary">{{ $post->published_at->translatedFormat('d F Y') }}</span>
                                </div>

                                <a href="{{ route('blog.show', $post->slug) }}" class="group">
                                    <h3 class="text-lg font-bold text-text font-heading group-hover:text-primary transition-colors line-clamp-2 mb-2">
                                        {{ $post->title }}
                                    </h3>
                                </a>

                                <p class="text-text-secondary text-sm line-clamp-2 flex-grow">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 130) }}
                                </p>

                                <a href="{{ route('blog.show', $post->slug) }}"
                                   class="mt-4 inline-flex items-center gap-1 text-primary font-semibold text-sm hover:gap-2 transition-all">
                                    {{ __('Baca Selengkapnya') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-10">
                        {{ $posts->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>

            {{-- ── Sidebar ─────────────────────────────── --}}
            <aside class="space-y-6 lg:sticky lg:top-24 self-start">

                {{-- Search --}}
                <div class="bg-white rounded-xl border border-border p-5">
                    <h3 class="font-bold text-text font-heading mb-3">{{ __('Cari Artikel') }}</h3>
                    <form action="{{ route('blog.index') }}" method="GET" class="relative">
                        @if($categorySlug)
                            <input type="hidden" name="category" value="{{ $categorySlug }}">
                        @endif
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="{{ __('Ketik kata kunci...') }}"
                               class="w-full !pl-9 !py-2.5 text-sm">
                    </form>
                </div>

                {{-- CTA Booking --}}
                <div class="rounded-xl p-6 text-white relative overflow-hidden"
                     style="background: linear-gradient(135deg, #2D6A4F 0%, #40916C 100%);">
                    <div class="relative z-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-white/15 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-lg mb-1">{{ __('Jelajahi Kebun Kopi') }}</h3>
                        <p class="text-sm text-white/85 mb-4 leading-relaxed">
                            {{ __('Rasakan pengalaman memetik kopi langsung dari pohonnya dan belajar proses pengolahannya.') }}
                        </p>
                        <a href="{{ route('packages.index') }}"
                           class="inline-flex w-full items-center justify-center bg-white text-primary font-semibold text-sm py-2.5 rounded-lg hover:bg-primary-50 transition-colors">
                            {{ __('Booking Tur Edukasi') }}
                        </a>
                    </div>
                </div>

                {{-- Popular categories --}}
                @if($popularCategories->isNotEmpty())
                <div class="bg-white rounded-xl border border-border p-5">
                    <h3 class="font-bold text-text font-heading mb-3">{{ __('Kategori Populer') }}</h3>
                    <ul class="divide-y divide-border">
                        @foreach($popularCategories as $cat)
                            @if($cat->posts_count > 0)
                                <li>
                                    <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
                                       class="flex items-center justify-between py-2.5 text-sm group">
                                        <span class="text-text-secondary group-hover:text-primary transition-colors">
                                            {{ $cat->name }}
                                        </span>
                                        <span class="inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 text-xs font-semibold rounded-md bg-primary-50 text-primary">
                                            {{ $cat->posts_count }}
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Newsletter --}}
                <div class="bg-white rounded-xl border border-border p-5">
                    <h3 class="font-bold text-text font-heading mb-1">{{ __('Berlangganan Info') }}</h3>
                    <p class="text-sm text-text-secondary mb-4">
                        {{ __('Dapatkan update artikel terbaru seputar kopi dan promo tur langsung ke email Anda.') }}
                    </p>
                    <form action="#" method="POST" class="space-y-2.5"
                          onsubmit="event.preventDefault(); alert('{{ __('Terima kasih, fitur ini segera hadir.') }}');">
                        @csrf
                        <input type="email" name="email" required
                               placeholder="{{ __('Email Anda') }}"
                               class="w-full text-sm">
                        <button type="submit" class="btn btn-primary w-full !py-2.5">
                            {{ __('Subscribe') }}
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
