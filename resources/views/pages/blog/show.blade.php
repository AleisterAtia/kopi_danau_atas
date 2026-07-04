@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160))

@push('head')
    @include('partials.navbar-light-override')
@endpush

@section('content')
@php
    $words   = str_word_count(strip_tags((string) $post->content));
    $readMin = max(1, (int) ceil($words / 200));
@endphp

<div class="pt-28 pb-10 bg-bg-warm">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="mb-6">
            @include('partials.breadcrumbs', ['items' => [
                ['label' => __('Beranda'), 'url' => route('home')],
                ['label' => __('Blog'),    'url' => route('blog.index')],
                ['label' => $post->title],
            ]])
        </div>

        {{-- Back link --}}
        <a href="{{ route('blog.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-text-secondary hover:text-primary transition-colors mb-8">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('Kembali ke Blog') }}
        </a>

        {{-- Article header --}}
        <header class="mb-8">
            @if($post->category)
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                   class="inline-block text-primary text-xs font-bold uppercase tracking-wider mb-4 hover:underline">
                    {{ $post->category->name }}
                </a>
            @endif

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-text font-heading leading-tight tracking-tight">
                {{ $post->title }}
            </h1>

            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-text-secondary">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $post->published_at->translatedFormat('d F Y') }}
                </span>
                <span class="w-1 h-1 rounded-full bg-text-muted"></span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $readMin }} {{ __('Menit Baca') }}
                </span>
            </div>
        </header>

        @if($post->thumbnail)
            <div class="rounded-2xl overflow-hidden shadow-sm mb-10 aspect-[16/9]">
                <img src="{{ Storage::url($post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
        @endif
    </div>
</div>

<div class="pb-16 bg-bg-warm">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <article class="prose prose-lg max-w-none text-text-secondary
                        prose-headings:font-heading prose-headings:text-text
                        prose-a:text-primary hover:prose-a:text-primary-light
                        prose-strong:text-text prose-img:rounded-xl">
            {!! $post->content !!}
        </article>

        {{-- Social Share --}}
        <div class="mt-12 pt-8 border-t border-border flex flex-wrap items-center justify-between gap-4">
            <span class="font-semibold text-text">{{ __('Bagikan artikel ini:') }}</span>
            <div class="flex gap-3">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener"
                   class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors" aria-label="Facebook">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener"
                   class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition-colors" aria-label="Twitter">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                </a>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - ' . request()->fullUrl()) }}" target="_blank" rel="noopener"
                   class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition-colors" aria-label="WhatsApp">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>

@if($relatedPosts->count() > 0)
<div class="py-16 bg-white border-t border-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold font-heading text-text mb-8">{{ __('Artikel Terkait') }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedPosts as $related)
            <article class="bg-white rounded-2xl overflow-hidden border border-border hover:shadow-lg transition-all duration-300">
                <a href="{{ route('blog.show', $related->slug) }}" class="relative aspect-[16/10] overflow-hidden block group">
                    @if($related->thumbnail)
                        <img src="{{ Storage::url($related->thumbnail) }}" alt="{{ $related->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full bg-primary-light flex items-center justify-center">
                            <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </a>

                <div class="p-5">
                    <div class="text-xs text-text-secondary mb-2">{{ $related->published_at->translatedFormat('d F Y') }}</div>
                    <h3 class="text-base font-bold text-text font-heading line-clamp-2 hover:text-primary transition-colors">
                        <a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}</a>
                    </h3>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection
