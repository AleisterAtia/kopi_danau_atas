@extends('layouts.app')

@section('title', __('Pesanan Saya'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading">{{ __('Pesanan Saya') }}</h1>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            @if($bookings->isEmpty())
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Belum ada pesanan') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Anda belum membuat pesanan paket wisata apapun.') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('packages.index') }}" class="btn-primary text-sm px-4 py-2">
                            {{ __('Lihat Paket Wisata') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-bg-warm border-b border-border">
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Kode & Paket') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Tgl Kunjungan') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Total') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Status') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody id="booking-rows" class="divide-y divide-border">
                            @include('pages.booking._rows')
                        </tbody>
                    </table>
                </div>
                
                @if($bookings->hasPages())
                <div class="px-6 py-4 border-t border-border">
                    {{ $bookings->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Background polling (like Filament's), so an admin changing a booking's
    // status shows up here without the visible full-page reload/flicker a
    // meta-refresh would cause. Keeps the current pagination query string.
    const bookingRows = document.getElementById('booking-rows');
    if (bookingRows) {
        setInterval(() => {
            fetch('{{ route('booking.poll') }}' + window.location.search)
                .then(res => res.text())
                .then(html => { bookingRows.innerHTML = html; });
        }, 2000);
    }
</script>
@endpush
