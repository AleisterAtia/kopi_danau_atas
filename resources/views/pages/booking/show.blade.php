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
            <div class="mt-4 sm:mt-0" id="booking-status-badge">
                @include('pages.booking._status_badge', ['booking' => $booking])
            </div>
        </div>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div id="booking-detail" class="space-y-8">
            @include('pages.booking._detail', ['booking' => $booking])
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

@push('scripts')
<script>
    // Mirrors the "Pesanan Saya" list page's polling, so a payment or an
    // admin check-in shows up here without a manual reload. Split into two
    // regions (status badge + detail card) instead of swapping the whole
    // page so the review form below — which isn't polled — never loses an
    // in-progress draft.
    const statusBadge = document.getElementById('booking-status-badge');
    const bookingDetail = document.getElementById('booking-detail');
    if (statusBadge && bookingDetail) {
        setInterval(() => {
            fetch('{{ route('booking.show.poll', $booking) }}')
                .then(res => res.text())
                .then(html => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    statusBadge.innerHTML = temp.querySelector('#booking-status-badge').innerHTML;
                    bookingDetail.innerHTML = temp.querySelector('#booking-detail').innerHTML;
                });
        }, 2000);
    }
</script>
@endpush
