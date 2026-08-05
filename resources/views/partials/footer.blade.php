<footer class="footer pt-14 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            {{-- Brand --}}
            <div class="lg:col-span-1">
                <h3 class="text-lg font-bold text-white mb-3 tracking-tight">Kopi Danau Diatas</h3>
                <p class="text-sm text-white/60 leading-relaxed mb-5">
                    {{ __('Agrowisata kopi edukatif yang memadukan keindahan alam Solok dengan cita rasa kopi Arabika premium langsung dari kebun kami.') }}
                </p>
                <div class="flex gap-3">
                    @if($ig = \App\Models\SiteSetting::getValue('instagram_url'))
                    <a href="{{ $ig }}" target="_blank" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    @endif
                    @if($fb = \App\Models\SiteSetting::getValue('facebook_url'))
                    <a href="{{ $fb }}" target="_blank" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                    @endif
                    @if($tt = \App\Models\SiteSetting::getValue('tiktok_url'))
                    <a href="{{ $tt }}" target="_blank" class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">{{ __('Navigasi') }}</h4>
                <ul class="space-y-2.5 text-sm text-white/60">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Beranda') }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ __('Tentang Kami') }}</a></li>
                    <li><a href="{{ route('packages.index') }}" class="hover:text-white transition-colors">{{ __('Paket Wisata') }}</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">{{ __('Blog') }}</a></li>
                    <li><a href="{{ route('home') }}#faq" class="hover:text-white transition-colors">{{ __('FAQ') }}</a></li>
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">{{ __('Masuk') }}</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">{{ __('Daftar') }}</a></li>
                    @else
                        <li><a href="{{ route('booking.index') }}" class="hover:text-white transition-colors">{{ __('Pesanan Saya') }}</a></li>
                    @endguest
                </ul>
            </div>

            {{-- Contact --}}
            <div class="lg:col-span-2">
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">{{ __('Hubungi Kami') }}</h4>
                <ul class="space-y-3 text-sm text-white/60">
                    <li class="flex items-start gap-3">
                        <svg class="h-4 w-4 text-white/40 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ \App\Models\SiteSetting::getValue('company_address', 'Alahan Panjang, Kec. Lembah Gumanti, Kab. Solok, Sumatera Barat') }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="h-4 w-4 text-white/40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ \App\Models\SiteSetting::getValue('company_phone', '+62 812-3456-7890') }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="h-4 w-4 text-white/40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ \App\Models\SiteSetting::getValue('company_email', 'info@kopidanauatas.com') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p class="text-xs text-white/40">&copy; {{ date('Y') }} CV Kopi Danau Diatas. {{ __('Semua hak cipta dilindungi.') }}</p>
            <p class="text-xs text-white/30">{{ __('Danau Diatas, Kabupaten Solok — Sumatera Barat') }}</p>
        </div>
    </div>
</footer>
