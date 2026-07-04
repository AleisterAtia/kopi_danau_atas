@extends('layouts.app')

@section('title', __('Pembayaran') . ' ' . $booking->booking_code)

@section('content')
<div class="pt-24 pb-8 bg-primary">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('booking.index') }}" class="inline-flex items-center text-primary-lighter hover:text-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('Kembali ke Daftar Pesanan') }}
        </a>
        <h1 class="text-3xl font-bold text-white font-heading mb-2">{{ __('Pembayaran Pesanan') }}</h1>
        <p class="text-primary-lighter font-mono">{{ $booking->booking_code }}</p>
    </div>
</div>

<div class="py-12 bg-bg min-h-screen" x-data="checkoutProcess()">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center">
                    @if($booking->tourPackage->images->first())
                        <img src="{{ Storage::url($booking->tourPackage->images->first()->image_path) }}" class="w-20 h-20 rounded-xl object-cover mr-6 shadow-sm">
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 font-heading">{{ $booking->tourPackage->name }}</h2>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }} • {{ $booking->guest_count }} {{ __('Orang') }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 md:p-8">
                <h3 class="font-bold text-gray-900 mb-4">{{ __('Rincian Pembayaran') }}</h3>
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('Harga Paket') }} ({{ $booking->guest_count }}x)</span>
                        <span>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                        <span class="font-bold text-gray-900">{{ __('Total') }}</span>
                        <span class="text-2xl font-bold text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-8 text-sm text-blue-800 flex items-start">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        {{ __('Selesaikan pembayaran Anda menggunakan Midtrans payment gateway yang aman. Anda dapat memilih berbagai metode pembayaran (Transfer Bank, E-Wallet, Kartu Kredit, dll).') }}
                    </div>
                </div>

                <button @click="pay" :disabled="loading" class="w-full btn-primary justify-center text-lg py-4 relative">
                    <span x-show="!loading">{{ __('Bayar Sekarang') }}</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Memproses...') }}
                    </span>
                </button>
            </div>
        </div>
        
        <div class="text-center text-sm text-gray-500 flex items-center justify-center space-x-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <span>{{ __('Pembayaran aman dan terenkripsi oleh Midtrans') }}</span>
        </div>
    </div>
</div>

<!-- Midtrans Snap JS -->
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    function checkoutProcess() {
        return {
            loading: false,
            pay() {
                this.loading = true;
                
                fetch('{{ route("payment.snap", $booking) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.loading = false;
                    if(data.snap_token) {
                        window.snap.pay(data.snap_token, {
                            onSuccess: function(result){
                                // Sync status with Midtrans API before redirecting
                                fetch('{{ route("payment.update-status", $booking) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                }).finally(() => {
                                    window.location.href = '{{ route("booking.show", $booking) }}';
                                });
                            },
                            onPending: function(result){
                                fetch('{{ route("payment.update-status", $booking) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                }).finally(() => {
                                    window.location.href = '{{ route("booking.show", $booking) }}';
                                });
                            },
                            onError: function(result){
                                alert(@js(__('Pembayaran gagal!')));
                                console.log(result);
                            },
                            onClose: function(){
                                console.log('Kustomer menutup popup tanpa menyelesaikan pembayaran');
                            }
                        });
                    } else {
                        alert(@js(__('Gagal mendapatkan token pembayaran.')));
                    }
                })
                .catch(error => {
                    this.loading = false;
                    alert(@js(__('Terjadi kesalahan sistem.')));
                    console.error(error);
                });
            }
        }
    }
</script>
@endsection
