{{--
    Override navbar agar tampil solid (latar putih, teks gelap) sejak awal,
    tanpa harus menunggu user scroll. Dipakai di halaman-halaman dengan
    background terang seperti blog, login, register, verify-email, dll.

    Cara pakai (di Blade halaman):
        @push('head')
            @include('partials.navbar-light-override')
        @endpush
--}}
<style>
    #navbar.navbar--top {
        background-color: rgba(255,255,255,0.97);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border-bottom: 1px solid var(--color-border);
    }
    #navbar.navbar--top .nav-link  { color: var(--color-text); }
    #navbar.navbar--top .nav-logo  { color: var(--color-primary); }
    #navbar.navbar--top .nav-link:hover { color: var(--color-primary); }
    #navbar.navbar--top .nav-link::after { background: var(--color-primary); }
</style>
