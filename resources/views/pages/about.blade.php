@extends('layouts.app')

@section('title', __('Tentang Kami'))

@push('head')
<style>
[x-cloak]{display:none!important}

.ab{
    --g1:var(--color-primary);
    --g2:var(--color-primary-light);
    --g3:var(--color-primary-lighter);
    --g0:var(--color-primary-50);
    --ink:var(--color-text);
    --soft:var(--color-text-secondary);
    --muted:var(--color-text-muted);
    --line:var(--color-border);
    --cream:var(--color-bg-warm);
    color:var(--ink);
}
.ab-wrap{max-width:72rem;margin-inline:auto;padding-inline:1.25rem;}
@media(min-width:640px){.ab-wrap{padding-inline:2rem;}}
.ab section{position:relative;}

.ab-eye{display:inline-flex;align-items:center;gap:.55rem;font-size:.75rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--g3);}
.ab-eye::before{content:"";width:26px;height:2px;background:var(--g3);border-radius:2px;}
.ab-eye--light{color:rgba(255,255,255,.72);}
.ab-eye--light::before{background:rgba(255,255,255,.5);}
.ab-h2{font-size:clamp(1.7rem,3vw,2.3rem);font-weight:800;letter-spacing:-.01em;color:var(--ink);line-height:1.15;margin:.9rem 0 0;}
.ab-body{color:var(--soft);font-size:1.02rem;line-height:1.75;}
.ab-body p{margin-bottom:1rem;}

/* ── Header (calm inner-page band) ── */
.ab-head{background:linear-gradient(135deg,var(--g2),var(--g1) 75%);color:#fff;padding:7.5rem 0 4rem;}
.ab-crumb{display:flex;gap:.5rem;align-items:center;font-size:.8rem;color:rgba(255,255,255,.6);margin-bottom:1.5rem;}
.ab-crumb a{color:rgba(255,255,255,.6);transition:color .2s;}
.ab-crumb a:hover{color:#fff;}
.ab-crumb svg{width:.85rem;height:.85rem;color:rgba(255,255,255,.4);}
.ab-head h1{font-size:clamp(2rem,4vw,2.9rem);font-weight:800;letter-spacing:-.015em;line-height:1.12;margin:1rem 0 1rem;max-width:20ch;}
.ab-head p{color:rgba(255,255,255,.8);font-size:1.05rem;line-height:1.6;max-width:52ch;}

/* ── Profil / Cerita ── */
.ab-profil{padding:5rem 0;background:#fff;}
.ab-profil__grid{display:grid;gap:2.5rem;align-items:center;}
@media(min-width:1024px){.ab-profil__grid{grid-template-columns:1fr 1.05fr;gap:4rem;}}
.ab-collage{display:grid;grid-template-columns:repeat(12,1fr);gap:.75rem;height:23rem;}
.ab-collage figure{margin:0;overflow:hidden;border-radius:.9rem;background:var(--g0);}
.ab-collage img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease;}
.ab-collage figure:hover img{transform:scale(1.04);}
.ab-collage .c1{grid-column:span 7;grid-row:span 2;}
.ab-collage .c2{grid-column:span 5;}
.ab-collage .c3{grid-column:span 5;}
.ab-collage--solo figure{grid-column:span 12;grid-row:span 2;}
.ab-facts{display:flex;flex-wrap:wrap;gap:1.5rem 2rem;margin-top:1.75rem;padding-top:1.5rem;border-top:1px solid var(--line);}
.ab-fact__n{font-size:1.55rem;font-weight:800;color:var(--g1);line-height:1;}
.ab-fact__u{font-size:.9rem;font-weight:700;color:var(--g3);margin-left:.15rem;}
.ab-fact__l{display:block;margin-top:.35rem;font-size:.82rem;color:var(--muted);}

/* ── Visi & Misi ── */
.ab-vm{padding:5rem 0;background:var(--cream);}
.ab-vm__grid{display:grid;gap:2rem;align-items:stretch;margin-top:2rem;}
@media(min-width:900px){.ab-vm__grid{grid-template-columns:1fr 1fr;gap:1.75rem;}}
.ab-panelcard{background:#fff;border:1px solid var(--line);border-radius:1rem;padding:2rem;}
.ab-panelcard h3{font-size:1.15rem;font-weight:800;color:var(--g1);margin-bottom:1rem;display:flex;align-items:center;gap:.6rem;}
.ab-panelcard h3 svg{width:1.25rem;height:1.25rem;color:var(--g3);}
.ab-panelcard>p{color:var(--soft);font-size:1.02rem;line-height:1.7;}
.ab-misi{list-style:none;margin:0;padding:0;display:grid;gap:.9rem;}
.ab-misi li{display:flex;gap:.75rem;align-items:flex-start;color:var(--soft);font-size:.97rem;line-height:1.55;}
.ab-misi svg{flex:none;width:1.15rem;height:1.15rem;color:var(--g3);margin-top:.15rem;}

/* ── Nilai ── */
.ab-nilai{padding:5rem 0;background:#fff;}
.ab-nilai__grid{display:grid;gap:1.1rem;grid-template-columns:1fr;margin-top:2.25rem;}
@media(min-width:640px){.ab-nilai__grid{grid-template-columns:repeat(2,1fr);}}
@media(min-width:1024px){.ab-nilai__grid{grid-template-columns:repeat(4,1fr);}}
.ab-nilai__c{border:1px solid var(--line);border-radius:.9rem;padding:1.5rem;transition:transform .25s,box-shadow .25s;}
.ab-nilai__c:hover{transform:translateY(-4px);box-shadow:0 18px 34px -22px rgba(27,67,50,.4);}
.ab-nilai__ic{width:2.75rem;height:2.75rem;border-radius:.7rem;background:var(--g0);color:var(--g1);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;}
.ab-nilai__ic svg{width:1.4rem;height:1.4rem;}
.ab-nilai__c h3{font-size:1.05rem;font-weight:700;color:var(--ink);margin-bottom:.4rem;}
.ab-nilai__c p{color:var(--soft);font-size:.9rem;line-height:1.6;}

/* ── Varietas (interaktif) ── */
.ab-var{padding:5rem 0;background:var(--cream);}
.ab-var__head{max-width:42rem;}
.ab-tabs{display:flex;flex-wrap:wrap;gap:.55rem;margin:1.75rem 0;}
.ab-tab{border:1px solid var(--line);background:#fff;color:var(--soft);padding:.55rem 1.15rem;border-radius:2rem;font-size:.88rem;font-weight:600;cursor:pointer;transition:all .2s;}
.ab-tab:hover{border-color:var(--g3);color:var(--g1);}
.ab-tab.is-active{background:var(--g1);color:#fff;border-color:var(--g1);}
.ab-panel{background:#fff;border:1px solid var(--line);border-radius:1.1rem;overflow:hidden;display:grid;grid-template-columns:1fr;}
@media(min-width:768px){.ab-panel{grid-template-columns:1fr 1fr;}}
.ab-panel__img{position:relative;min-height:15rem;background:var(--g0);}
.ab-panel__img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
.ab-panel__img svg{position:absolute;inset:0;margin:auto;width:3.5rem;height:3.5rem;color:rgba(27,67,50,.2);}
.ab-panel__body{padding:2rem;display:flex;flex-direction:column;justify-content:center;}
.ab-panel__body h3{font-size:1.5rem;font-weight:800;color:var(--ink);}
.ab-origin{margin-top:.5rem;display:inline-flex;align-items:center;gap:.4rem;color:var(--g2);font-weight:600;font-size:.9rem;}
.ab-origin svg{width:1rem;height:1rem;}
.ab-panel__body .ab-body{font-size:.94rem;margin-top:.9rem;}
.ab-notes{margin-top:1.25rem;}
.ab-notes__l{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.55rem;}
.ab-chip{display:inline-block;padding:.32rem .75rem;border-radius:2rem;background:var(--g0);color:var(--g1);font-size:.78rem;font-weight:600;margin:0 .4rem .4rem 0;}

/* ── Galeri ── */
.ab-gal{padding:5rem 0;background:#fff;}
/* Stage stacks every rotating page in the same grid cell so the incoming and
   outgoing page overlap and crossfade instead of the layout collapsing to
   zero height between them (which read as a jarring "reload"). */
.ab-gal__stage{display:grid;margin-top:2.25rem;}
.ab-gal__grid{grid-area:1/1;display:grid;grid-template-columns:repeat(2,1fr);gap:.85rem;}
@media(min-width:768px){.ab-gal__grid{grid-template-columns:repeat(4,1fr);}}
.ab-gal__i{position:relative;overflow:hidden;border-radius:.8rem;cursor:zoom-in;background:var(--g0);border:0;padding:0;aspect-ratio:1;}
.ab-gal__i img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s ease;}
.ab-gal__i:hover img{transform:scale(1.06);}
.ab-lightbox{position:fixed;inset:0;z-index:80;background:rgba(15,30,22,.92);display:flex;align-items:center;justify-content:center;padding:1.5rem;cursor:zoom-out;}
.ab-lightbox img{max-width:min(92vw,1000px);max-height:88vh;border-radius:.5rem;}
.ab-lightbox__x{position:absolute;top:1.25rem;right:1.5rem;color:#fff;background:rgba(255,255,255,.12);border:0;width:2.5rem;height:2.5rem;border-radius:50%;font-size:1.4rem;line-height:1;cursor:pointer;}

/* ── Lokasi ── */
.ab-loc{padding:5rem 0;background:var(--cream);}
.ab-loc__grid{display:grid;gap:2.25rem;align-items:stretch;margin-top:1.75rem;}
@media(min-width:1024px){.ab-loc__grid{grid-template-columns:1fr 1.1fr;}}
.ab-loc__list{list-style:none;padding:0;margin:1.5rem 0;display:grid;gap:1rem;}
.ab-loc__list li{display:flex;gap:.85rem;align-items:flex-start;color:var(--soft);}
.ab-loc__list svg{flex:none;width:1.25rem;height:1.25rem;color:var(--g3);margin-top:.15rem;}
.ab-social{display:flex;gap:.65rem;}
.ab-social a{width:2.5rem;height:2.5rem;border-radius:.7rem;background:#fff;border:1px solid var(--line);color:var(--g1);display:flex;align-items:center;justify-content:center;transition:all .2s;}
.ab-social a:hover{background:var(--g1);color:#fff;border-color:var(--g1);}
.ab-social svg{width:1.1rem;height:1.1rem;}
.ab-map{border-radius:1rem;overflow:hidden;border:1px solid var(--line);min-height:20rem;background:var(--g0);}
.ab-map iframe{width:100%;height:100%;min-height:20rem;border:0;display:block;}
.ab-map__fb{display:flex;align-items:center;justify-content:center;gap:.5rem;height:100%;min-height:20rem;color:var(--g1);font-weight:600;}

/* ── CTA ── */
.ab-cta{background:linear-gradient(135deg,var(--g2),var(--g1) 80%);color:#fff;padding:4rem 0;text-align:center;}
.ab-cta h2{font-size:clamp(1.7rem,3vw,2.2rem);font-weight:800;margin-bottom:.85rem;}
.ab-cta p{color:rgba(255,255,255,.8);max-width:40rem;margin:0 auto 1.75rem;}
.ab-cta__btn{display:inline-flex;align-items:center;gap:.55rem;background:#fff;color:var(--g1);font-weight:700;padding:.85rem 1.7rem;border-radius:.7rem;transition:transform .2s,box-shadow .2s;}
.ab-cta__btn:hover{transform:translateY(-2px);box-shadow:0 16px 30px -16px rgba(0,0,0,.4);}
.ab-cta__btn svg{width:1.1rem;height:1.1rem;}

.ab [data-reveal]{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease;}
.ab [data-reveal].revealed{opacity:1;transform:none;}
@media(prefers-reduced-motion:reduce){
    .ab *,.ab *::before,.ab *::after{transition:none!important;animation:none!important;}
    .ab [data-reveal]{opacity:1;transform:none;}
}
</style>
@endpush

@section('content')
<div class="ab">

    {{-- ══ HEADER ══ --}}
    <section class="ab-head">
        <div class="ab-wrap">
            <nav class="ab-crumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">{{ __('Beranda') }}</a>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span style="color:#fff;font-weight:500;">{{ __('Tentang Kami') }}</span>
            </nav>
            <span class="ab-eye ab-eye--light">{{ __('Tentang Kami') }}</span>
            <h1>{{ $about->title ?? __('Tentang CV Kopi Danau Diatas') }}</h1>
            <p>{{ __('CV Kopi Danau Diatas memadukan kebun Arabika dataran tinggi Solok dengan wisata edukasi — dari kebun hingga cangkir.') }}</p>
        </div>
    </section>

    {{-- ══ PROFIL / CERITA ══ --}}
    @if($about)
    <section class="ab-profil">
        <div class="ab-wrap">
            <div class="ab-profil__grid">
                <div class="ab-collage {{ $about->images->count() < 2 ? 'ab-collage--solo' : '' }}" data-reveal>
                    @forelse($about->images->take(3) as $i => $img)
                        <figure class="c{{ $i + 1 }}">
                            <img src="{{ Storage::url($img->image_path) }}" alt="{{ $about->title }}" loading="lazy" decoding="async">
                        </figure>
                    @empty
                        <figure class="c1"><svg style="margin:auto;width:3.5rem;height:3.5rem;color:rgba(27,67,50,.2)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg></figure>
                    @endforelse
                </div>

                <div data-reveal>
                    <span class="ab-eye">{{ __('Profil Perusahaan') }}</span>
                    <h2 class="ab-h2">{{ $about->title }}</h2>
                    <div class="ab-body" style="margin-top:1.1rem;">{!! $about->description !!}</div>

                    <div class="ab-facts">
                        <div class="ab-fact">
                            <span><span class="ab-fact__n">{{ $altitude }}</span><span class="ab-fact__u">mdpl</span></span>
                            <span class="ab-fact__l">{{ __('Ketinggian Kebun') }}</span>
                        </div>
                        <div class="ab-fact">
                            <span class="ab-fact__n">{{ $varieties->count() }}</span>
                            <span class="ab-fact__l">{{ __('Varietas Arabika') }}</span>
                        </div>
                        <div class="ab-fact">
                            <span><span class="ab-fact__n">100</span><span class="ab-fact__u">%</span></span>
                            <span class="ab-fact__l">{{ __('Arabika Solok') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ══ VISI & MISI ══ --}}
    <section class="ab-vm">
        <div class="ab-wrap">
            <span class="ab-eye" data-reveal>{{ __('Arah Kami') }}</span>
            <div class="ab-vm__grid">
                <div class="ab-panelcard" data-reveal>
                    <h3>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ __('Visi') }}
                    </h3>
                    <p>{{ $vision }}</p>
                </div>
                <div class="ab-panelcard" data-reveal>
                    <h3>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        {{ __('Misi') }}
                    </h3>
                    <ul class="ab-misi">
                        @foreach($mission as $misi)
                            <li>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $misi }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ NILAI ══ --}}
    <section class="ab-nilai">
        <div class="ab-wrap">
            <div data-reveal>
                <span class="ab-eye">{{ __('Prinsip') }}</span>
                <h2 class="ab-h2">{{ __('Nilai yang Kami Pegang') }}</h2>
            </div>
            <div class="ab-nilai__grid">
                @foreach($values as $value)
                    <div class="ab-nilai__c" data-reveal>
                        <div class="ab-nilai__ic">
                            <x-dynamic-component :component="'heroicon-o-'.$value->icon" />
                        </div>
                        <h3>{{ $value->title }}</h3>
                        <p>{{ $value->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ VARIETAS (interaktif) ══ --}}
    @if($varieties->isNotEmpty())
    <section class="ab-var" x-data="{ active: 0 }">
        <div class="ab-wrap">
            <div class="ab-var__head" data-reveal>
                <span class="ab-eye">{{ __('Kebun Kami') }}</span>
                <h2 class="ab-h2">{{ __('Varietas Kopi Kami') }}</h2>
            </div>

            <div class="ab-tabs" data-reveal>
                @foreach($varieties as $variety)
                    <button type="button" class="ab-tab" :class="active === {{ $loop->index }} ? 'is-active' : ''" @click="active = {{ $loop->index }}">{{ $variety->name }}</button>
                @endforeach
            </div>

            @foreach($varieties as $variety)
                <div x-show="active === {{ $loop->index }}" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="ab-panel">
                        <div class="ab-panel__img">
                            @if($variety->image_path)
                                <img src="{{ Storage::url($variety->image_path) }}" alt="{{ $variety->name }}" loading="lazy" decoding="async">
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                            @endif
                        </div>
                        <div class="ab-panel__body">
                            <h3>{{ $variety->name }}</h3>
                            @if($variety->origin)
                                <span class="ab-origin">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ __('Asal') }}: {{ $variety->origin }}
                                </span>
                            @endif
                            @if($variety->description)
                                <div class="ab-body">{!! $variety->description !!}</div>
                            @endif
                            @if($variety->flavor_profile)
                                <div class="ab-notes">
                                    <div class="ab-notes__l">{{ __('Profil Rasa') }}</div>
                                    @foreach(array_filter(array_map('trim', explode(',', $variety->flavor_profile))) as $note)
                                        <span class="ab-chip">{{ $note }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ══ GALERI ══ --}}
    @if($gallery->isNotEmpty())
    @php $galleryPages = $gallery->chunk(8)->values(); @endphp
    <section class="ab-gal" x-data="{ open:false, src:'', alt:'', page:0 }"
             @if($galleryPages->count() > 1) x-init="setInterval(() => page = (page + 1) % {{ $galleryPages->count() }}, 5000)" @endif>
        <div class="ab-wrap">
            <div data-reveal>
                <span class="ab-eye">{{ __('Galeri') }}</span>
                <h2 class="ab-h2">{{ __('Kebun & Kegiatan Kami') }}</h2>
            </div>
            <div class="ab-gal__stage" data-reveal>
                @foreach($galleryPages as $i => $chunk)
                    <div class="ab-gal__grid" x-show="page === {{ $i }}"
                         @if($i > 0) x-cloak @endif
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-700"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        @foreach($chunk as $img)
                            @php $url = Storage::url($img->image_path); @endphp
                            <button type="button" class="ab-gal__i"
                                    @click="src='{{ $url }}'; alt='{{ addslashes($img->caption ?? '') }}'; open=true"
                                    aria-label="{{ __('Perbesar gambar') }}">
                                <img src="{{ $url }}" alt="{{ $img->caption ?? __('Galeri') }}" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="ab-lightbox" x-show="open" x-cloak x-transition.opacity
             @click="open=false" @keydown.escape.window="open=false">
            <button type="button" class="ab-lightbox__x" @click.stop="open=false" aria-label="{{ __('Tutup') }}">&times;</button>
            <img :src="src" :alt="alt" @click.stop>
        </div>
    </section>
    @endif

    {{-- ══ LOKASI ══ --}}
    <section class="ab-loc">
        <div class="ab-wrap">
            <div data-reveal><span class="ab-eye">{{ __('Kunjungi') }}</span></div>
            <div class="ab-loc__grid">
                <div data-reveal>
                    <h2 class="ab-h2">{{ __('Temukan Kami di Danau Diatas') }}</h2>
                    <ul class="ab-loc__list">
                        @if($settings['company_address'])
                        <li>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $settings['company_address'] }}</span>
                        </li>
                        @endif
                        @if($settings['company_phone'])
                        <li>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $settings['company_phone'] }}</span>
                        </li>
                        @endif
                        @if($settings['company_email'])
                        <li>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $settings['company_email'] }}</span>
                        </li>
                        @endif
                    </ul>
                    <div class="ab-social">
                        @if($settings['instagram_url'])
                        <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener" aria-label="Instagram">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        @endif
                        @if($settings['facebook_url'])
                        <a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                        @endif
                        @if($settings['company_whatsapp'])
                        <a href="https://wa.me/{{ $settings['company_whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="ab-map" data-reveal>
                    @if($settings['google_maps_embed'])
                        {!! $settings['google_maps_embed'] !!}
                    @else
                        <div class="ab-map__fb">
                            <svg style="width:1.3rem;height:1.3rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            {{ __('Danau Diatas, Kabupaten Solok — Sumatera Barat') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══ CTA ══ --}}
    <section class="ab-cta">
        <div class="ab-wrap">
            <h2>{{ __('Siap menjelajahi kebun kami?') }}</h2>
            <p>{{ __('Ikuti paket agrowisata kami dan rasakan langsung proses kopi dari kebun ke cangkir.') }}</p>
            <a href="{{ route('packages.index') }}" class="ab-cta__btn">
                {{ __('Lihat Paket Wisata') }}
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
(function () {
    var reveals = document.querySelectorAll('.ab [data-reveal]');
    if (!reveals.length) return;
    var ro = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('revealed'); ro.unobserve(e.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(function (el) { ro.observe(el); });
})();
</script>
@endpush
