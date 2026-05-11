@extends('layouts.app')

@section('title', __('Pesanan Saya'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading">{{ __('Pesanan Saya') }}</h1>
    </div>
</div>

<div class="py-12 bg-bg min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Kode & Paket') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Tgl Kunjungan') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Total') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider">{{ __('Status') }}</th>
                                <th class="py-4 px-6 font-semibold text-gray-600 uppercase text-xs tracking-wider text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($bookings as $booking)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-mono text-sm text-gray-500 mb-1">{{ $booking->booking_code }}</div>
                                    <div class="font-bold text-gray-900">{{ $booking->tourPackage->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->guest_count }} {{ __('Orang') }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ __('Dipesan') }}: {{ $booking->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @if($booking->status == 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Menunggu Pembayaran') }}</span>
                                    @elseif($booking->status == 'paid')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Lunas') }}</span>
                                    @elseif($booking->status == 'confirmed')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('Terkonfirmasi') }}</span>
                                    @elseif($booking->status == 'completed')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Selesai') }}</span>
                                    @elseif($booking->status == 'cancelled')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('Dibatalkan') }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    @if($booking->status == 'pending')
                                        <a href="{{ route('payment.checkout', $booking) }}" class="inline-flex items-center text-sm bg-primary text-white px-3 py-1.5 rounded hover:bg-primary-light transition-colors">
                                            {{ __('Bayar') }}
                                        </a>
                                    @endif
                                    @if(in_array($booking->status, ['paid', 'confirmed', 'completed']))
                                        <a href="{{ route('booking.invoice', $booking) }}" class="inline-flex items-center text-sm border border-green-300 bg-green-50 text-green-700 px-3 py-1.5 rounded hover:bg-green-100 transition-colors" title="{{ __('Download Invoice') }}">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ __('Invoice') }}
                                        </a>
                                    @endif
                                    <a href="{{ route('booking.show', $booking) }}" class="inline-flex items-center text-sm border border-gray-300 bg-white text-gray-700 px-3 py-1.5 rounded hover:bg-gray-50 transition-colors">
                                        {{ __('Detail') }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($bookings->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $bookings->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
