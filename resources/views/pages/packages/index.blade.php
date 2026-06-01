@extends('layouts.app')

@section('title', __('Paket Wisata'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading mb-4">{{ __('Paket Wisata Agrowisata Kopi') }}</h1>
        <p class="text-xl text-primary-lighter max-w-2xl mx-auto">{{ __('Pilih pengalaman wisata edukatif Anda di CV Kopi Danau Atas. Mari rasakan menjadi petani kopi dalam sehari.') }}</p>
    </div>
</div>

<div class="py-12 bg-bg min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($packages->isEmpty())
            <div class="text-center py-20">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Belum ada paket wisata') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Kami sedang menyiapkan pengalaman terbaik untuk Anda. Silakan kembali lagi nanti.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($packages as $package)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full border border-gray-100">
                    <div class="relative h-56 overflow-hidden group">
                        @if($package->images->first())
                            <img src="{{ Storage::url($package->images->first()->image_path) }}" alt="{{ $package->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" decoding="async">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-bold text-primary shadow-sm">
                            Rp {{ number_format($package->price, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex items-center text-sm text-text-secondary mb-3 space-x-4">
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $package->duration_hours }} Jam</span>
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Max {{ $package->daily_capacity }} pax</span>
                        </div>
                        
                        <h3 class="text-xl font-bold text-text mb-2 font-heading">{{ $package->name }}</h3>
                        
                        <div class="text-text-secondary text-sm mb-6 flex-grow line-clamp-3">
                            {!! strip_tags($package->description) !!}
                        </div>
                        
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                            <div class="flex items-center">
                                <span class="text-warning text-sm mr-1">★</span>
                                <span class="text-sm font-bold">{{ $package->average_rating > 0 ? $package->average_rating : '-' }}</span>
                                <span class="text-xs text-gray-500 ml-1">({{ $package->reviews_count ?? 0 }} ulasan)</span>
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
