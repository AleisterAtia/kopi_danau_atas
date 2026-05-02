@extends('layouts.app')

@section('title', __('Verifikasi Email'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-bg-warm py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <div class="w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-6 text-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                
                <h2 class="text-center text-2xl font-bold font-heading text-text mb-4">
                    {{ __('Verifikasi Email Anda') }}
                </h2>

                <div class="text-center text-sm text-text-secondary mb-6 leading-relaxed">
                    {{ __('Terima kasih telah mendaftar! Sebelum mulai memesan paket wisata, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda.') }}
                </div>

                @if (session('message'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="mt-8 flex flex-col items-center space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            {{ __('Kirim Ulang Email Verifikasi') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="btn btn-outline w-full !border-gray-200 !text-text-secondary hover:!bg-gray-50 hover:!text-text">
                            {{ __('Keluar Akun') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
