@extends('layouts.app')

@section('title', __('Atur Ulang Kata Sandi'))

@push('head')
    @include('partials.navbar-light-override')
@endpush

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-bg py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-primary font-heading">
                {{ __('Atur Ulang Kata Sandi') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('Buat kata sandi baru untuk akun Anda.') }}
            </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-6 space-y-5" action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alamat Email') }}</label>
                <input id="email" name="email" type="email" autocomplete="email" required
                    value="{{ old('email', $email) }}"
                    class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Kata Sandi Baru') }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                    class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="••••••••">
                <p class="mt-1 text-xs text-gray-500">{{ __('Minimal 8 karakter.') }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Konfirmasi Kata Sandi') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="••••••••">
            </div>

            <div>
                <button type="submit" class="w-full btn-primary justify-center py-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Simpan Kata Sandi Baru') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
