@php
    $toasts = collect()
        ->when(session('success'), fn ($c) => $c->push(['type' => 'success', 'message' => session('success')]))
        ->when(session('status'), fn ($c) => $c->push(['type' => 'success', 'message' => session('status')]))
        ->when(session('error'), fn ($c) => $c->push(['type' => 'error', 'message' => session('error')]))
        ->when($errors->any(), fn ($c) => $c->concat(
            collect($errors->all())->map(fn ($e) => ['type' => 'error', 'message' => $e])
        ))
        ->values()
        ->map(fn ($t, $i) => $t + ['id' => $i]);
@endphp
@if($toasts->isNotEmpty())
<div
    x-data="{ toasts: @js($toasts) }"
    x-init="toasts.forEach(t => setTimeout(() => toasts = toasts.filter(x => x.id !== t.id), 5000))"
    class="fixed top-24 right-4 z-[100] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-end="opacity-0"
            class="flex items-start gap-3 rounded-lg shadow-lg border-l-4 bg-white p-4"
            :class="toast.type === 'success' ? 'border-green-500' : 'border-red-500'"
        >
            <svg x-show="toast.type === 'success'" class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <svg x-show="toast.type === 'error'" class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <p class="text-sm flex-1" :class="toast.type === 'success' ? 'text-green-700' : 'text-red-700'" x-text="toast.message"></p>
            <button type="button" @click="toasts = toasts.filter(x => x.id !== toast.id)" class="text-gray-400 hover:text-gray-600 leading-none text-lg">&times;</button>
        </div>
    </template>
</div>
@endif
