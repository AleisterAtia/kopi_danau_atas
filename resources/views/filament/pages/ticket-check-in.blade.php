<x-filament-panels::page>
    <div class="max-w-xl space-y-6">
        <form wire:submit="submit" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="token" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Token / hasil scan QR
                </label>
                <input
                    id="token"
                    type="text"
                    wire:model="token"
                    autofocus
                    autocomplete="off"
                    placeholder="Tempel token atau pindai QR e-tiket…"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                />
            </div>
            <x-filament::button type="submit" icon="heroicon-o-check-badge">
                Validasi & Check-in
            </x-filament::button>
        </form>

        @if ($outcome)
            @php
                $colors = [
                    'success' => ['bg' => 'bg-green-50 dark:bg-green-900/20', 'border' => 'border-green-500', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'CHECK-IN BERHASIL'],
                    'already_checked_in' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'border' => 'border-amber-500', 'text' => 'text-amber-800 dark:text-amber-200', 'label' => 'SUDAH CHECK-IN'],
                    'not_eligible' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'border' => 'border-red-500', 'text' => 'text-red-800 dark:text-red-200', 'label' => 'BELUM LUNAS / TIDAK VALID'],
                    'not_found' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'border' => 'border-red-500', 'text' => 'text-red-800 dark:text-red-200', 'label' => 'TIKET TIDAK DITEMUKAN'],
                ];
                $c = $colors[$outcome['result']] ?? $colors['not_found'];
                $b = $outcome['booking'];
            @endphp

            <div class="rounded-xl border-l-4 {{ $c['border'] }} {{ $c['bg'] }} p-5">
                <p class="text-sm font-bold tracking-wide {{ $c['text'] }}">{{ $c['label'] }}</p>

                @if ($b)
                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Kode Booking</dt>
                            <dd class="font-mono font-semibold text-gray-900 dark:text-white">{{ $b['code'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($b['status']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Paket</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $b['package'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Tanggal Kunjungan</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $b['visit_date'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Pemesan</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $b['guest_name'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Jumlah Tamu</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $b['guest_count'] }} orang</dd>
                        </div>
                        @if ($b['checked_in_at'])
                            <div class="col-span-2">
                                <dt class="text-gray-500 dark:text-gray-400">Check-in pada</dt>
                                <dd class="text-gray-900 dark:text-white">
                                    {{ $b['checked_in_at'] }}@if ($b['checked_in_by']) · oleh {{ $b['checked_in_by'] }}@endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
