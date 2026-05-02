@extends('layouts.app')

@section('title', __('Blog Edukasi Kopi'))

@section('content')
<div class="pt-24 pb-12 bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white font-heading mb-4">{{ __('Blog & Edukasi') }}</h1>
        <p class="text-xl text-primary-lighter max-w-2xl mx-auto">{{ __('Pelajari lebih dalam tentang kopi Arabika Solok, proses pengolahan, dan tips agrowisata dari para ahli kami.') }}</p>
    </div>
</div>

<div class="py-12 bg-bg min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Categories filter could go here if needed -->
        @if($categories->count() > 0)
        <div class="flex flex-wrap justify-center gap-2 mb-12">
            <a href="{{ route('blog.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !request()->has('category') ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-gray-100' }}">
                {{ __('Semua') }}
            </a>
            @foreach($categories as $category)
                @if($category->posts_count > 0)
                <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium bg-white text-gray-600 hover:bg-gray-100">
                    {{ $category->name }} ({{ $category->posts_count }})
                </a>
                @endif
            @endforeach
        </div>
        @endif

        @if($posts->isEmpty())
            <div class="text-center py-20">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 8M6 12h12M6 16h12M6 8h12" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Belum ada artikel') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Kami sedang menyiapkan konten menarik untuk Anda.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full border border-gray-100">
                    <a href="{{ route('blog.show', $post->slug) }}" class="relative h-56 overflow-hidden block group">
                        @if($post->thumbnail)
                            <img src="{{ Storage::url($post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full bg-primary-light flex items-center justify-center">
                                <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        @if($post->category)
                        <div class="absolute top-4 left-4 bg-accent text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">
                            {{ $post->category->name }}
                        </div>
                        @endif
                    </a>
                    
                    <div class="p-6 flex flex-col flex-grow">
                        <div class="text-xs text-gray-500 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $post->published_at->format('d M Y') }}
                        </div>
                        
                        <a href="{{ route('blog.show', $post->slug) }}" class="group">
                            <h3 class="text-xl font-bold text-text mb-3 font-heading group-hover:text-primary transition-colors line-clamp-2">{{ $post->title }}</h3>
                        </a>
                        
                        <div class="text-text-secondary text-sm mb-6 flex-grow line-clamp-3">
                            {!! strip_tags($post->content) !!}
                        </div>
                        
                        <div class="mt-auto pt-4 border-t border-gray-100">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-primary font-bold hover:text-accent-light flex items-center text-sm transition-colors">
                                {{ __('Baca Selengkapnya') }} <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
