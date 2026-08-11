@extends('layouts.app')

@section('title', __('Detail Pesanan') . ' ' . $booking->booking_code)

@section('content')
<div class="pt-24 pb-8 bg-primary">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('booking.index') }}" class="inline-flex items-center text-primary-lighter hover:text-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('Kembali ke Daftar Pesanan') }}
        </a>
        <div class="flex flex-wrap items-center justify-between">
            <h1 class="text-3xl font-bold text-white font-heading">{{ __('Pesanan') }} <span class="font-mono text-accent-light">{{ $booking->booking_code }}</span></h1>
            <div class="mt-4 sm:mt-0">
                @if($booking->status == 'pending')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">{{ __('Menunggu Pembayaran') }}</span>
                @elseif($booking->status == 'paid')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">{{ __('Lunas') }}</span>
                @elseif($booking->status == 'confirmed')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">{{ __('Terkonfirmasi') }}</span>
                @elseif($booking->status == 'completed')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">{{ __('Selesai') }}</span>
                @elseif($booking->status == 'cancelled')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800">{{ __('Dibatalkan') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        {{-- E-Ticket QR Code (paid/confirmed/completed) --}}
        @if(in_array($booking->status, ['paid', 'confirmed', 'completed']) && $booking->qr_code_path)
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="flex-shrink-0 bg-bg-warm p-4 rounded-xl border border-border">
                        <img src="{{ Storage::url($booking->qr_code_path) }}" alt="E-Ticket QR" class="w-48 h-48 object-contain">
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wider rounded-full mb-3">
                            {{ __('E-Tiket Aktif') }}
                        </span>
                        <h2 class="text-2xl font-bold font-heading text-gray-900 mb-2">{{ __('Tunjukkan QR ini saat tiba') }}</h2>
                        <p class="text-gray-600 text-sm mb-4">{{ __('Petugas kami akan memindai kode ini untuk memverifikasi pesanan Anda.') }}</p>
                        <div class="inline-block bg-gray-900 text-white font-mono text-sm px-4 py-2 rounded-lg tracking-wider">
                            {{ $booking->booking_code }}
                        </div>
                        @if($booking->checked_in_at)
                        <p class="mt-3 text-sm text-green-700 flex items-center justify-center md:justify-start">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ __('Sudah check-in pada') }} {{ \Carbon\Carbon::parse($booking->checked_in_at)->format('d M Y H:i') }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($booking->status == 'pending')
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between shadow-sm gap-4">
            <div class="mb-4 sm:mb-0">
                <h3 class="text-lg font-bold text-yellow-800 mb-1">{{ __('Menunggu Pembayaran') }}</h3>
                <p class="text-sm text-yellow-700">{{ __('Silakan selesaikan pembayaran Anda untuk mengamankan kuota kunjungan.') }}</p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                <form method="POST" action="{{ route('payment.update-status', $booking) }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="btn btn-outline !border-yellow-400 !text-yellow-800 hover:!bg-yellow-100 whitespace-nowrap w-full sm:w-auto justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        {{ __('Cek Status') }}
                    </button>
                </form>
                <a href="{{ route('payment.checkout', $booking) }}" class="btn-primary whitespace-nowrap w-full sm:w-auto text-center justify-center">
                    {{ __('Bayar Sekarang') }}
                </a>
                <form method="POST" action="{{ route('booking.cancel', $booking) }}" class="w-full sm:w-auto"
                      onsubmit="return confirm('{{ __('Batalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.') }}');">
                    @csrf
                    <button type="submit" class="btn btn-outline !border-red-300 !text-red-700 hover:!bg-red-50 whitespace-nowrap w-full sm:w-auto justify-center">
                        {{ __('Batalkan') }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Guest Info Card --}}
        @if($booking->guest_name || $booking->guest_phone || $booking->guest_email || $booking->notes)
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <div class="p-6 md:p-8">
                <h2 class="text-xl font-bold font-heading mb-4 text-gray-900 border-b border-border pb-2">{{ __('Data Pemesan') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    @if($booking->guest_name)
                    <div>
                        <span class="block text-xs text-gray-500 uppercase mb-1">{{ __('Nama Lengkap') }}</span>
                        <span class="font-medium text-gray-900">{{ $booking->guest_name }}</span>
                    </div>
                    @endif
                    @if($booking->guest_email)
                    <div>
                        <span class="block text-xs text-gray-500 uppercase mb-1">{{ __('Alamat Email') }}</span>
                        <span class="font-medium text-gray-900 break-all">{{ $booking->guest_email }}</span>
                    </div>
                    @endif
                    @if($booking->guest_phone)
                    <div>
                        <span class="block text-xs text-gray-500 uppercase mb-1">{{ __('No. WhatsApp') }}</span>
                        <span class="font-medium text-gray-900">{{ $booking->guest_phone }}</span>
                    </div>
                    @endif
                    @if($booking->notes)
                    <div class="md:col-span-2">
                        <span class="block text-xs text-gray-500 uppercase mb-1">{{ __('Catatan Khusus') }}</span>
                        <p class="text-gray-700 bg-bg-warm p-3 rounded-lg whitespace-pre-line">{{ $booking->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Package Info -->
                    <div>
                        <h2 class="text-xl font-bold font-heading mb-4 text-gray-900 border-b border-border pb-2">{{ __('Informasi Paket') }}</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                @if($booking->tourPackage->images->first())
                                    <img src="{{ Storage::url($booking->tourPackage->images->first()->image_path) }}" class="w-16 h-16 rounded-lg object-cover mr-4">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-primary-50 flex items-center justify-center mr-4">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ $booking->tourPackage->name }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center mt-1">
                                        <svg class="w-4 h-4 mr-1 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $booking->tourPackage->duration_hours }} {{ __('Jam') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4 bg-bg-warm p-4 rounded-lg">
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">{{ __('Tanggal Kunjungan') }}</span>
                                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">{{ __('Jumlah Peserta') }}</span>
                                    <span class="font-medium text-gray-900">{{ $booking->guest_count }} {{ __('Orang') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div>
                        <h2 class="text-xl font-bold font-heading mb-4 text-gray-900 border-b border-border pb-2">{{ __('Rincian Harga') }}</h2>
                        <div class="space-y-3 bg-bg-warm p-4 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $booking->tourPackage->name }} (x{{ $booking->guest_count }})</span>
                                <span class="text-gray-900">Rp {{ number_format($booking->tourPackage->price * $booking->guest_count, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-border pt-3 mt-3 flex justify-between items-center">
                                <span class="font-bold text-gray-900">{{ __('Total Pembayaran') }}</span>
                                <span class="text-xl font-bold text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if($booking->payment)
                        <div class="mt-4 text-sm text-gray-600">
                            <p class="mb-1"><strong>{{ __('Metode:') }}</strong> {{ strtoupper(str_replace('_', ' ', $booking->payment->payment_type)) }}</p>
                            @if($booking->payment->paid_at)
                            <p><strong>{{ __('Dibayar Pada:') }}</strong> {{ \Carbon\Carbon::parse($booking->payment->paid_at)->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                        @endif

                        @if(in_array($booking->status, ['paid', 'confirmed', 'completed']))
                        <div class="mt-6">
                            <a href="{{ route('booking.invoice', $booking) }}" 
                               class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-light transition-colors shadow-sm"
                               id="btn-download-invoice">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                {{ __('Download Invoice') }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Section (Only if completed) -->
        @if($booking->status == 'completed')
            <div class="bg-white rounded-xl border border-border overflow-hidden">
                <div class="p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading mb-6 text-gray-900">{{ __('Ulasan Pengalaman Anda') }}</h2>
                    
                    @if($booking->review)
                        <div class="bg-bg-warm p-6 rounded-xl border border-border">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex text-warning">
                                    @for($i=1; $i<=5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $booking->review->rating ? 'text-warning' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($booking->review->created_at)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            <p class="text-gray-700 italic">"{{ $booking->review->comment }}"</p>
                            <p class="mt-3 text-xs text-green-700 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ __('Ulasan Anda telah dipublikasikan') }}
                            </p>
                        </div>
                    @else
                        <form action="{{ route('review.store', $booking) }}" method="POST" class="space-y-6" x-data="{ rating: 5, hover: 0 }">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Penilaian Anda') }}</label>
                                <div class="flex text-3xl cursor-pointer">
                                    <template x-for="i in 5">
                                        <svg @click="rating = i" @mouseenter="hover = i" @mouseleave="hover = 0" 
                                             class="w-8 h-8 transition-colors duration-150" 
                                             :class="(hover ? hover >= i : rating >= i) ? 'text-warning' : 'text-gray-300'"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </template>
                                </div>
                                <input type="hidden" name="rating" x-model="rating">
                            </div>

                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bagaimana pengalaman Anda?') }}</label>
                                <textarea id="comment" name="comment" rows="4" required class="block w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary shadow-sm" placeholder="{{ __('Tuliskan pengalaman Anda mengikuti paket wisata ini... (min. 10 karakter)') }}"></textarea>
                            </div>

                            <button type="submit" class="btn-primary">
                                {{ __('Kirim Ulasan') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
