@props(['package', 'wishlisted' => false])

@auth
    <button type="button"
        x-data="{ wishlisted: @js($wishlisted) }"
        @click.prevent="
            fetch('{{ route('wishlist.toggle', $package) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                wishlisted = data.wishlisted;
                $dispatch('wishlist-toggled', { packageId: {{ $package->id }}, wishlisted: data.wishlisted });
            })
        "
        :aria-pressed="wishlisted"
        :title="wishlisted ? @js(__('Hapus dari favorit')) : @js(__('Simpan sebagai favorit'))"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center']) }}
    >
        <svg class="w-5 h-5" :class="wishlisted ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-current'" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
    </button>
@else
    <a href="{{ route('login') }}"
        title="{{ __('Masuk untuk menyimpan favorit') }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center']) }}
    >
        <svg class="w-5 h-5 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
    </a>
@endauth
