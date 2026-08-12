@props(['code', 'title', 'message', 'primaryLabel', 'primaryUrl', 'secondaryLabel' => null, 'secondaryUrl' => null])

<div class="min-h-[70vh] flex items-center justify-center px-4 py-24 bg-bg-warm">
    <div class="max-w-md w-full text-center">
        <p class="text-7xl font-extrabold text-primary/15 font-heading tracking-tight mb-2">{{ $code }}</p>
        <h1 class="text-2xl font-bold text-text font-heading mb-3">{{ $title }}</h1>
        <p class="text-text-secondary leading-relaxed mb-8">{{ $message }}</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ $primaryUrl }}" class="btn btn-primary">{{ $primaryLabel }}</a>
            @if($secondaryLabel)
                <a href="{{ $secondaryUrl }}" class="btn btn-outline">{{ $secondaryLabel }}</a>
            @endif
        </div>
    </div>
</div>
