@extends('layouts.app')

@section('title', __('Profil Saya'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading">{{ __('Pengaturan Profil') }}</h1>
    </div>
</div>

<div class="py-12 bg-bg min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="flex items-center mb-8 pb-8 border-b border-gray-100">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-24 h-24 rounded-full object-cover border-4 border-gray-50 mr-6">
                    @else
                        <div class="w-24 h-24 rounded-full bg-primary text-white flex items-center justify-center text-3xl font-bold border-4 border-gray-50 mr-6">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading">{{ auth()->user()->name }}</h2>
                        <p class="text-gray-500">{{ auth()->user()->email }}</p>
                        <div class="mt-2 inline-block px-3 py-1 bg-accent/10 text-accent font-medium text-xs rounded-full uppercase tracking-wider">
                            {{ auth()->user()->role == 'admin' ? __('Administrator') : __('Pelanggan') }}
                        </div>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Lengkap') }}</label>
                            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alamat Email') }}</label>
                            <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nomor Telepon / WhatsApp') }}</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 font-heading mb-4">{{ __('Ubah Kata Sandi') }} <span class="text-sm font-normal text-gray-500">({{ __('Biarkan kosong jika tidak ingin mengubah') }})</span></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Kata Sandi Baru') }}</label>
                                <input type="password" id="password" name="password" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20" placeholder="Minimal 8 karakter">
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
