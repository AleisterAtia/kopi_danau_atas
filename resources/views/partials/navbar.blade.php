{{-- AYO-style navbar: transparent on hero, white on scroll --}}
<nav id="navbar" class="navbar navbar--top fixed w-full z-50 top-0" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-[72px]">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('images/logo-navbar.png') }}" alt="CV Kopi Danau Diatas" class="h-10 w-auto">
            </a>

            {{-- Desktop nav --}}
            <div class="hidden lg:flex items-center gap-8">
                <a href="{{ route('home') }}" class="nav-link">{{ __('Beranda') }}</a>
                <a href="{{ route('about') }}" class="nav-link">{{ __('Tentang Kami') }}</a>
                <a href="{{ route('packages.index') }}" class="nav-link">{{ __('Paket Wisata') }}</a>
                <a href="{{ route('blog.index') }}" class="nav-link">{{ __('Blog') }}</a>
                <a href="{{ route('home') }}#faq" class="nav-link">{{ __('FAQ') }}</a>

                {{-- Language toggle --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="nav-link flex items-center gap-1">
                        <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        {{ strtoupper(app()->getLocale()) }}
                    </button>
                    <div x-show="open" x-transition.opacity class="absolute right-0 mt-3 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                        <a href="{{ route('locale.switch', 'id') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 {{ app()->getLocale() == 'id' ? 'font-semibold text-primary' : '' }}">
                            🇮🇩 Indonesia
                        </a>
                        <a href="{{ route('locale.switch', 'en') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 {{ app()->getLocale() == 'en' ? 'font-semibold text-primary' : '' }}">
                            🇬🇧 English
                        </a>
                    </div>
                </div>

                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 nav-link">
                            @if(auth()->user()->avatarUrl())
                                <img src="{{ auth()->user()->avatarUrl() }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <span class="hidden xl:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition.opacity class="absolute right-0 mt-3 w-52 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-50">
                                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            @if(auth()->user()->isAdmin())
                                <a href="/admin" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ __('Admin Panel') }}
                                </a>
                            @endif
                            <a href="{{ route('booking.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                {{ __('Pesanan Saya') }}
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ __('Profil') }}
                            </a>
                            <div class="border-t border-gray-50 mt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        {{ __('Keluar') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-link">{{ __('Masuk') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary text-sm !py-2.5 !px-5">{{ __('Daftar') }}</a>
                @endauth
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden nav-link p-1">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden bg-white border-t border-gray-100 shadow-lg">
        <div class="px-4 py-4 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50 font-medium">{{ __('Beranda') }}</a>
            <a href="{{ route('about') }}" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50 font-medium">{{ __('Tentang Kami') }}</a>
            <a href="{{ route('packages.index') }}" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50 font-medium">{{ __('Paket Wisata') }}</a>
            <a href="{{ route('blog.index') }}" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50 font-medium">{{ __('Blog') }}</a>
            <a href="{{ route('home') }}#faq" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50 font-medium">{{ __('FAQ') }}</a>

            <div class="border-t border-gray-100 my-2 pt-2">
                <div class="flex gap-3 px-3 py-2">
                    <a href="{{ route('locale.switch', 'id') }}" class="text-sm font-medium {{ app()->getLocale() == 'id' ? 'text-primary' : 'text-gray-400' }}">🇮🇩 ID</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="text-sm font-medium {{ app()->getLocale() == 'en' ? 'text-primary' : 'text-gray-400' }}">🇬🇧 EN</a>
                </div>
            </div>

            @auth
                <div class="border-t border-gray-100 my-2 pt-2" x-data="{ accountOpen: false }">
                    <button type="button" @click="accountOpen = !accountOpen" class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50 font-medium">
                        <span class="flex items-center gap-2">
                            @if(auth()->user()->avatarUrl())
                                <img src="{{ auth()->user()->avatarUrl() }}" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            @endif
                            {{ auth()->user()->name }}
                        </span>
                        <svg class="w-4 h-4 opacity-50 transition-transform duration-200" :class="accountOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accountOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-end="opacity-0">
                        @if(auth()->user()->isAdmin())
                            <a href="/admin" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50">{{ __('Admin Panel') }}</a>
                        @endif
                        <a href="{{ route('booking.index') }}" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50">{{ __('Pesanan Saya') }}</a>
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-2.5 rounded-lg text-gray-800 hover:bg-gray-50">{{ __('Profil') }}</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="block w-full text-left px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Keluar') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="border-t border-gray-100 my-2 pt-3 flex gap-3 px-3">
                    <a href="{{ route('login') }}" class="btn btn-outline flex-1 text-center">{{ __('Masuk') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary flex-1 text-center">{{ __('Daftar') }}</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
