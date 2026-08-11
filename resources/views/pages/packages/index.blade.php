@extends('layouts.app')

@section('title', __('Paket Wisata'))

@section('content')
<div class="pt-28 pb-16 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="hero-eyebrow mb-4">{{ __('Paket Wisata') }}</p>
        <h1 class="text-4xl font-bold text-white font-heading mb-4">{{ __('Paket Wisata Agrowisata Kopi') }}</h1>
        <p class="text-lg text-primary-lighter max-w-2xl mx-auto">{{ __('Pilih pengalaman wisata edukatif Anda di CV Kopi Danau Diatas. Mari rasakan menjadi petani kopi dalam sehari.') }}</p>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('packages.index') }}">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                {{-- ── Sidebar filter ─────────────────────── --}}
                <aside class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-border p-5 lg:sticky lg:top-24 space-y-6">

                        {{-- Search --}}
                        <div>
                            <label for="q" class="block text-sm font-semibold text-text mb-2">{{ __('Cari Paket') }}</label>
                            <div class="relative">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" name="q" id="q" value="{{ $search }}" placeholder="{{ __('Cari paket...') }}"
                                    class="w-full !pl-9 text-sm">
                            </div>
                        </div>

                        {{-- Kategori --}}
                        @if($categories->count())
                        <div>
                            <h3 class="text-sm font-semibold text-text mb-3">{{ __('Kategori') }}</h3>
                            <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                @foreach($categories as $category)
                                <label class="flex items-center gap-2.5 cursor-pointer text-sm text-text-secondary hover:text-text transition-colors">
                                    <input type="checkbox" name="category[]" value="{{ $category->slug }}" @checked(in_array($category->slug, $categorySlugs))
                                        class="rounded border-border text-primary focus:ring-primary/30">
                                    <span>{{ $category->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Rentang Harga --}}
                        <div>
                            <h3 class="text-sm font-semibold text-text mb-3">{{ __('Rentang Harga') }}</h3>
                            <div class="flex items-center gap-2">
                                <input type="number" min="0" step="1000" name="price_min" value="{{ $priceMin }}" placeholder="{{ __('Rp Min') }}"
                                    class="w-full text-sm" aria-label="{{ __('Harga min') }}">
                                <span class="text-text-muted">—</span>
                                <input type="number" min="0" step="1000" name="price_max" value="{{ $priceMax }}" placeholder="{{ __('Rp Max') }}"
                                    class="w-full text-sm" aria-label="{{ __('Harga max') }}">
                            </div>
                        </div>

                        <div class="space-y-2 pt-1">
                            <button type="submit" class="btn-primary w-full justify-center text-sm">{{ __('Terapkan Filter') }}</button>
                            @if(request()->hasAny(['q', 'category', 'price_min', 'price_max']))
                                <a href="{{ route('packages.index') }}" class="block text-center text-sm text-text-secondary hover:text-primary py-1">{{ __('Reset Filter') }}</a>
                            @endif
                        </div>
                    </div>
                </aside>

                {{-- ── Results ─────────────────────────────── --}}
                <div class="lg:col-span-3">

                    {{-- Result count + sort --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <p class="text-sm text-text-secondary">
                            {{ __('Menampilkan') }} <span class="font-semibold text-text">{{ $packages->total() }}</span> {{ __('paket') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <label for="sort" class="text-sm text-text-secondary whitespace-nowrap">{{ __('Urutkan') }}</label>
                            <select name="sort" id="sort" onchange="this.form.submit()" class="text-sm py-2">
                                <option value="latest" @selected($sort === 'latest')>{{ __('Terbaru') }}</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Harga termurah') }}</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Harga termahal') }}</option>
                                <option value="rating" @selected($sort === 'rating')>{{ __('Rating tertinggi') }}</option>
                                <option value="name" @selected($sort === 'name')>{{ __('Nama (A-Z)') }}</option>
                            </select>
                        </div>
                    </div>

        @if($packages->isEmpty())
            <div class="text-center py-20 bg-white rounded-xl border border-border">
                <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                @if(request()->hasAny(['q', 'category', 'price_min', 'price_max']))
                    <h3 class="mt-2 text-sm font-medium text-text">{{ __('Tidak ada paket yang cocok') }}</h3>
                    <p class="mt-1 text-sm text-text-secondary">{{ __('Coba ubah kata kunci atau filter Anda.') }} <a href="{{ route('packages.index') }}" class="text-primary font-medium hover:underline">{{ __('Reset filter') }}</a></p>
                @else
                    <h3 class="mt-2 text-sm font-medium text-text">{{ __('Belum ada paket wisata') }}</h3>
                    <p class="mt-1 text-sm text-text-secondary">{{ __('Kami sedang menyiapkan pengalaman terbaik untuk Anda. Silakan kembali lagi nanti.') }}</p>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($packages as $package)
                    <x-package-card :package="$package" />
                @endforeach
            </div>

            <div class="mt-12">
                {{ $packages->links() }}
            </div>
        @endif
                </div>{{-- /results --}}
            </div>{{-- /grid --}}
        </form>
    </div>
</div>
@endsection
