@extends('layouts.app')

@section('title', __('Favorit Saya'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading">{{ __('Favorit Saya') }}</h1>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($packages->isEmpty())
            <div class="bg-white rounded-xl border border-border text-center py-16">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Belum ada paket favorit.') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Simpan paket wisata yang kamu suka untuk dilihat lagi nanti.') }}</p>
                <div class="mt-6">
                    <a href="{{ route('packages.index') }}" class="btn-primary text-sm px-4 py-2">
                        {{ __('Lihat Paket Wisata') }}
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($packages as $package)
                <div class="card flex flex-col h-full"
                     x-data="{ removed: false }"
                     x-show="!removed"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-end="opacity-0 scale-95"
                     @wishlist-toggled.window="if ($event.detail.packageId === {{ $package->id }} && !$event.detail.wishlisted) removed = true">
                    <div class="relative h-56 overflow-hidden group">
                        @if($package->images->first())
                            <img src="{{ Storage::url($package->images->first()->image_path) }}" alt="{{ $package->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                        @else
                            <div class="w-full h-full bg-primary-50 flex items-center justify-center">
                                <span class="text-text-muted text-sm">{{ __('Tanpa Gambar') }}</span>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-bold text-primary">
                            Rp {{ number_format($package->price, 0, ',', '.') }}
                        </div>
                        <x-wishlist-heart :package="$package" :wishlisted="true"
                            class="absolute top-4 left-4 w-9 h-9 rounded-full bg-white/95 backdrop-blur-sm text-text-secondary hover:text-red-500" />
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex items-center text-sm text-text-secondary mb-3 space-x-4">
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $package->duration_hours }} {{ __('Jam') }}</span>
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> {{ __('Maks.') }} {{ $package->daily_capacity }} {{ __('pax') }}</span>
                        </div>

                        <h3 class="text-xl font-bold text-text mb-2 font-heading">{{ $package->name }}</h3>

                        <div class="text-text-secondary text-sm mb-6 flex-grow line-clamp-3">
                            {!! strip_tags($package->description) !!}
                        </div>

                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-border">
                            <div class="flex items-center">
                                <span class="text-warning text-sm mr-1">★</span>
                                <span class="text-sm font-bold">{{ $package->average_rating > 0 ? $package->average_rating : '-' }}</span>
                                <span class="text-xs text-gray-500 ml-1">({{ $package->reviews_count ?? 0 }} {{ __('ulasan') }})</span>
                            </div>
                            <a href="{{ route('packages.show', $package->slug) }}" class="btn-primary text-sm px-4 py-1.5">
                                {{ __('Detail') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
