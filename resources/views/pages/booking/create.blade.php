@extends('layouts.app')

@section('title', __('Konfirmasi Pesanan'))

@section('content')
<div class="pt-24 pb-8 bg-primary">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('packages.show', $package->slug) }}" class="inline-flex items-center text-primary-lighter hover:text-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('Kembali ke Detail Paket') }}
        </a>

        {{-- Stepper --}}
        <div class="flex items-center gap-2 sm:gap-4 text-xs sm:text-sm text-white/80 mb-4">
            <span class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-white/20 text-white flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Pilih Paket') }}
            </span>
            <span class="text-white/40">›</span>
            <span class="flex items-center gap-2 text-white font-semibold">
                <span class="w-6 h-6 rounded-full bg-accent text-white flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Konfirmasi Data') }}
            </span>
            <span class="text-white/40">›</span>
            <span class="flex items-center gap-2 text-white/50">
                <span class="w-6 h-6 rounded-full bg-white/10 text-white/70 flex items-center justify-center text-xs font-bold">3</span>
                {{ __('Pembayaran') }}
            </span>
        </div>

        <h1 class="text-3xl font-bold text-white font-heading">{{ __('Konfirmasi Pesanan Anda') }}</h1>
        <p class="text-primary-lighter mt-1">{{ __('Tinjau detail paket dan lengkapi data diri sebelum melanjutkan ke pembayaran.') }}</p>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <form action="{{ route('booking.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tour_package_id" value="{{ $package->id }}">
            <input type="hidden" name="visit_date" value="{{ $visitDate }}">
            <input type="hidden" name="guest_count" value="{{ $guestCount }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT — Form Data Pemesan --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border border-border overflow-hidden">
                        <div class="p-6 md:p-8">
                            <div class="flex items-center justify-between mb-6 pb-4 border-b border-border">
                                <h2 class="text-xl font-bold font-heading text-gray-900">{{ __('Data Pemesan') }}</h2>
                                <span class="text-xs text-gray-500">{{ __('Wajib diisi') }} *</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <label for="guest_name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Nama Lengkap') }} *</label>
                                    <input type="text" id="guest_name" name="guest_name" required maxlength="255"
                                        value="{{ old('guest_name', auth()->user()->name) }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20"
                                        placeholder="{{ __('Nama sesuai identitas') }}">
                                </div>

                                <div>
                                    <label for="guest_email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Alamat Email') }} *</label>
                                    <input type="email" id="guest_email" name="guest_email" required maxlength="255"
                                        value="{{ old('guest_email', auth()->user()->email) }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20"
                                        placeholder="email@contoh.com">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('Bukti pesanan akan dikirim ke email ini.') }}</p>
                                </div>

                                <div>
                                    <label for="guest_phone" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('No. WhatsApp') }} *</label>
                                    <input type="tel" id="guest_phone" name="guest_phone" required maxlength="30"
                                        value="{{ old('guest_phone', auth()->user()->phone) }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20"
                                        placeholder="08xxxxxxxxxx">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('Untuk konfirmasi & komunikasi terkait kunjungan.') }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Catatan Khusus') }} <span class="text-gray-400">({{ __('opsional') }})</span></label>
                                    <textarea id="notes" name="notes" rows="3" maxlength="1000"
                                        class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20"
                                        placeholder="{{ __('Misal: alergi makanan, kebutuhan khusus, perkiraan waktu tiba, dll.') }}">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- T&C --}}
                    <div class="bg-white rounded-xl border border-border overflow-hidden">
                        <div class="p-6 md:p-8">
                            <h3 class="text-lg font-bold font-heading text-gray-900 mb-4">{{ __('Syarat & Ketentuan') }}</h3>
                            <div class="bg-bg-warm border border-border rounded-lg p-4 max-h-44 overflow-y-auto text-sm text-gray-600 leading-relaxed space-y-2 mb-4">
                                <p>{{ __('1. Pemesanan dianggap sah setelah pembayaran berhasil diverifikasi oleh sistem.') }}</p>
                                <p>{{ __('2. Pembayaran wajib diselesaikan dalam 1 (satu) jam setelah pemesanan dibuat. Pemesanan akan otomatis dibatalkan jika melewati batas waktu.') }}</p>
                                <p>{{ __('3. Pengunjung wajib hadir tepat waktu pada tanggal kunjungan yang dipesan.') }}</p>
                                <p>{{ __('4. Tiket yang sudah dibayar tidak dapat dikembalikan (non-refundable), kecuali dalam keadaan darurat dengan persetujuan pengelola.') }}</p>
                                <p>{{ __('5. Pengunjung wajib mengikuti aturan keselamatan dan instruksi pemandu selama berada di lokasi.') }}</p>
                                <p>{{ __('6. Data pribadi yang diberikan akan digunakan hanya untuk keperluan pemesanan dan komunikasi terkait kunjungan.') }}</p>
                            </div>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="terms" value="1" required
                                    class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm text-gray-700">
                                    {{ __('Saya telah membaca dan menyetujui') }} <a href="#" class="text-primary font-semibold hover:underline">{{ __('Syarat & Ketentuan') }}</a> {{ __('serta') }} <a href="#" class="text-primary font-semibold hover:underline">{{ __('Kebijakan Privasi') }}</a>.
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- RIGHT — Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-border overflow-hidden sticky top-28">
                        <div class="p-6 border-b border-border bg-bg-warm">
                            <h3 class="text-base font-bold font-heading text-gray-900">{{ __('Ringkasan Pesanan') }}</h3>
                        </div>

                        <div class="p-6 space-y-5">
                            <div class="flex gap-4">
                                @if($package->images->first())
                                    <img src="{{ Storage::url($package->images->first()->image_path) }}" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                                @else
                                    <div class="w-20 h-20 rounded-lg bg-primary-50 flex-shrink-0"></div>
                                @endif
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-900 leading-tight line-clamp-2">{{ $package->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $package->duration_hours }} {{ __('jam') }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3 pt-4 border-t border-border">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Tanggal Kunjungan') }}</span>
                                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($visitDate)->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Jumlah Peserta') }}</span>
                                    <span class="font-medium text-gray-900">{{ $guestCount }} {{ __('Orang') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Harga / orang') }}</span>
                                    <span class="font-medium text-gray-900">{{ \App\Support\Currency::format($package->price) }}</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-border">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">{{ __('Subtotal') }}</span>
                                    <span class="text-sm text-gray-900">{{ \App\Support\Currency::format($totalPrice) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-bold text-gray-900">{{ __('Total') }}</span>
                                    <span class="text-2xl font-bold text-primary">{{ \App\Support\Currency::format($totalPrice) }}</span>
                                </div>
                            </div>

                            <button type="submit" class="w-full btn-primary justify-center text-base py-3.5">
                                {{ __('Lanjut ke Pembayaran') }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </button>

                            <p class="text-xs text-center text-gray-500 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                {{ __('Pembayaran aman via Midtrans') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
