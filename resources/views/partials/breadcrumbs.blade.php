{{--
    Reusable breadcrumb partial.
    Usage:
        @include('partials.breadcrumbs', ['items' => [
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Blog'),    'url' => route('blog.index')],
            ['label' => $post->title], // last item, no url
        ]])
--}}
@php($items = $items ?? [])

@if(!empty($items))
<nav aria-label="Breadcrumb" class="text-sm">
    <ol class="flex flex-wrap items-center gap-1.5 text-text-secondary">
        @foreach($items as $i => $item)
            @php($isLast = $i === count($items) - 1)
            <li class="flex items-center gap-1.5 min-w-0">
                @if($isLast || empty($item['url']))
                    <span class="font-medium text-text truncate max-w-[16rem] md:max-w-md" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @else
                    <a href="{{ $item['url'] }}"
                       class="hover:text-primary transition-colors">
                        {{ $item['label'] }}
                    </a>
                @endif

                @unless($isLast)
                    <svg class="w-3.5 h-3.5 text-text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
@endif
