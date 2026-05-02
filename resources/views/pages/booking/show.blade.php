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

<div class="py-12 bg-bg min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if($booking->status == 'pending')
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between shadow-sm">
            <div class="mb-4 sm:mb-0">
                <h3 class="text-lg font-bold text-yellow-800 mb-1">{{ __('Menunggu Pembayaran') }}</h3>
                <p class="text-sm text-yellow-700">{{ __('Silakan selesaikan pembayaran Anda untuk mengamankan kuota kunjungan.') }}</p>
            </div>
            <a href="{{ route('payment.checkout', $booking) }}" class="btn-primary whitespace-nowrap bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto text-center justify-center">
                {{ __('Bayar Sekarang') }}
            </a>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Package Info -->
                    <div>
                        <h2 class="text-xl font-bold font-heading mb-4 text-gray-900 border-b border-gray-100 pb-2">{{ __('Informasi Paket') }}</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                @if($booking->tourPackage->images->first())
                                    <img src="{{ Storage::url($booking->tourPackage->images->first()->image_path) }}" class="w-16 h-16 rounded-lg object-cover mr-4">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ $booking->tourPackage->name }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center mt-1">
                                        <svg class="w-4 h-4 mr-1 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $booking->tourPackage->duration_hours }} Jam
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">{{ __('Tanggal Kunjungan') }}</span>
                                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">{{ __('Jumlah Peserta') }}</span>
                                    <span class="font-medium text-gray-900">{{ $booking->guest_count }} Orang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div>
                        <h2 class="text-xl font-bold font-heading mb-4 text-gray-900 border-b border-gray-100 pb-2">{{ __('Rincian Harga') }}</h2>
                        <div class="space-y-3 bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $booking->tourPackage->name }} (x{{ $booking->guest_count }})</span>
                                <span class="text-gray-900">Rp {{ number_format($booking->tourPackage->price * $booking->guest_count, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 mt-3 flex justify-between items-center">
                                <span class="font-bold text-gray-900">{{ __('Total Pembayaran') }}</span>
                                <span class="text-xl font-bold text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if($booking->payment)
                        <div class="mt-4 text-sm text-gray-600">
                            <p class="mb-1"><strong>Metode:</strong> {{ strtoupper(str_replace('_', ' ', $booking->payment->payment_type)) }}</p>
                            @if($booking->payment->paid_at)
                            <p><strong>Dibayar Pada:</strong> {{ \Carbon\Carbon::parse($booking->payment->paid_at)->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Section (Only if completed) -->
        @if($booking->status == 'completed')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading mb-6 text-gray-900">{{ __('Ulasan Pengalaman Anda') }}</h2>
                    
                    @if($booking->review)
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                            <div class="flex items-center mb-4">
                                <div class="flex text-warning">
                                    @for($i=1; $i<=5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $booking->review->rating ? 'text-warning' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <span class="ml-3 text-sm text-gray-500 font-medium px-2 py-1 bg-white rounded shadow-sm border border-gray-200">
                                    Status: 
                                    @if($booking->review->status == 'approved') <span class="text-green-600">Disetujui</span>
                                    @elseif($booking->review->status == 'rejected') <span class="text-red-600">Ditolak</span>
                                    @else <span class="text-yellow-600">Menunggu Moderasi</span>
                                    @endif
                                </span>
                            </div>
                            <p class="text-gray-700 italic">"{{ $booking->review->comment }}"</p>
                        </div>
                    @else
                        @if ($errors->any())
                            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
                                <ul class="list-disc list-inside text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

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
                                <textarea id="comment" name="comment" rows="4" required class="block w-full rounded-lg border-gray-300 focus:ring-primary focus:border-primary shadow-sm" placeholder="Tuliskan pengalaman Anda mengikuti paket wisata ini... (min. 10 karakter)"></textarea>
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
