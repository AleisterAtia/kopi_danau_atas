@extends('layouts.app')

@section('title', __('Lupa Kata Sandi'))

@push('head')
    @include('partials.navbar-light-override')
@endpush

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-bg-warm py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-sm border border-border">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-primary font-heading">
                {{ __('Lupa Kata Sandi?') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.') }}
            </p>
        </div>

        <form class="mt-6 space-y-6" action="{{ route('password.email') }}" method="POST">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alamat Email') }}</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                    class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="email@contoh.com">
            </div>

            <div>
                <button type="submit" class="w-full btn-primary justify-center py-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Kirim Tautan Reset') }}
                </button>
            </div>

            <p class="text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="font-medium text-accent hover:text-accent-light transition-colors">
                    &larr; {{ __('Kembali ke halaman masuk') }}
                </a>
            </p>
        </form>
    </div>
</div>
@endsection
