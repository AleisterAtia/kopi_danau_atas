@extends('layouts.app')

@section('title', __('Profil Saya'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading">{{ __('Pengaturan Profil') }}</h1>
    </div>
</div>

<div class="py-16 lg:py-20 bg-bg-warm min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        

        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="flex items-center mb-8 pb-8 border-b border-border">
                    <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data"
                        x-data="{ preview: null, uploading: false }" @submit="uploading = true">
                        @csrf
                        <label for="avatar" class="relative shrink-0 cursor-pointer group mr-6 block">
                            <template x-if="preview">
                                <img :src="preview" class="w-24 h-24 rounded-full object-cover border-4 border-bg-warm">
                            </template>
                            <template x-if="!preview">
                                @if(auth()->user()->avatarUrl())
                                    <img src="{{ auth()->user()->avatarUrl() }}" class="w-24 h-24 rounded-full object-cover border-4 border-bg-warm">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-primary text-white flex items-center justify-center text-3xl font-bold border-4 border-bg-warm">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </template>
                            <span class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center text-white text-xs font-semibold transition-opacity"
                                :class="uploading ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                <span x-show="!uploading">{{ __('Ganti Foto') }}</span>
                                <span x-show="uploading">{{ __('Mengunggah...') }}</span>
                            </span>
                            <input type="file" id="avatar" name="avatar" accept="image/*" class="sr-only"
                                @change="if ($event.target.files.length) { preview = URL.createObjectURL($event.target.files[0]); $el.form.submit(); }">
                        </label>
                    </form>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 font-heading">{{ auth()->user()->name }}</h2>
                        <p class="text-gray-500">{{ auth()->user()->email }}</p>
                        <div class="mt-2 inline-block px-3 py-1 bg-accent/10 text-accent font-medium text-xs rounded-full uppercase tracking-wider">
                            {{ auth()->user()->role == 'admin' ? __('Administrator') : __('Pelanggan') }}
                        </div>
                        @error('avatar')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-6"
                    x-data="{ email: @js(old('email', auth()->user()->email)), originalEmail: @js(auth()->user()->email), newPassword: '' }">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Lengkap') }}</label>
                            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Alamat Email') }}</label>
                            <input type="email" id="email" name="email" x-model="email" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nomor Telepon / WhatsApp') }}</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-border">
                        <h3 class="text-lg font-bold text-gray-900 font-heading mb-4">{{ __('Ubah Kata Sandi') }} <span class="text-sm font-normal text-gray-500">({{ __('Biarkan kosong jika tidak ingin mengubah') }})</span></h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Kata Sandi Baru') }}</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" x-model="newPassword" autocomplete="new-password" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 pr-10" placeholder="{{ __('Minimal 8 karakter') }}">
                                    <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" tabindex="-1" aria-label="{{ __('Tampilkan kata sandi') }}">
                                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 pr-10">
                                    <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" tabindex="-1" aria-label="{{ __('Tampilkan kata sandi') }}">
                                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Only shown when there's actually something to confirm — editing
                         name/phone alone never needs it, so it doesn't sit here looking
                         mandatory (and confusing Google-login users) for changes that
                         don't touch it. --}}
                    @if (auth()->user()->password)
                        <div class="pt-6 mt-6 border-t border-border" x-show="email.trim().toLowerCase() !== originalEmail.trim().toLowerCase() || newPassword.length > 0">
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Kata Sandi Saat Ini') }}
                            </label>
                            <p class="text-sm text-gray-500 mb-2">
                                {{ __('Wajib diisi karena Anda mengubah alamat email atau kata sandi.') }}
                            </p>
                            <div class="relative w-full md:w-1/2">
                                <input type="password" id="current_password" name="current_password" autocomplete="current-password" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary/20 pr-10">
                                <button type="button" onclick="togglePassword('current_password', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" tabindex="-1" aria-label="{{ __('Tampilkan kata sandi') }}">
                                    <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                </button>
                            </div>
                        </div>
                    @endif

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

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const eyeOpen = button.querySelector('.eye-open');
        const eyeClosed = button.querySelector('.eye-closed');

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
            button.setAttribute('aria-label', '{{ __("Sembunyikan kata sandi") }}');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
            button.setAttribute('aria-label', '{{ __("Tampilkan kata sandi") }}');
        }
    }
</script>
@endpush
