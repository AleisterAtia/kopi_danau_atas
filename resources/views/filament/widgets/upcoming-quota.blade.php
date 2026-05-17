<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Kuota 7 Hari ke Depan
        </x-slot>

        <x-slot name="description">
            Penggunaan kuota per paket wisata untuk satu pekan mendatang. Termasuk pemesanan <em>pending</em> yang dibuat &lt; 1 jam terakhir.
        </x-slot>

        @if (! $hasPackages)
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada paket wisata aktif.
            </div>
        @else
            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-y border-gray-200 dark:border-white/10 text-left">
                            <th class="px-6 py-2 font-semibold text-gray-700 dark:text-gray-200">Tanggal</th>
                            <th class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200">Paket</th>
                            <th class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200 text-center">Kapasitas</th>
                            <th class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200 text-center">Terpakai</th>
                            <th class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200 text-center">Sisa</th>
                            <th class="px-6 py-2 font-semibold text-gray-700 dark:text-gray-200">Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $prevDate = null; @endphp
                        @foreach ($rows as $row)
                            @php
                                $isNewDate = $prevDate !== $row['date']->toDateString();
                                $prevDate = $row['date']->toDateString();

                                $util = $row['utilization'];
                                $barColor = $util >= 100 ? 'bg-red-500' : ($util >= 75 ? 'bg-amber-500' : 'bg-emerald-500');
                                $availableColor = $row['available'] == 0
                                    ? 'text-red-600 dark:text-red-400'
                                    : ($row['available'] < 5 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400');
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-white/5 {{ $isNewDate ? 'border-t-2 border-gray-200 dark:border-white/10' : '' }}">
                                <td class="px-6 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    @if ($isNewDate)
                                        <div class="font-semibold">{{ $row['date']->translatedFormat('D, d M') }}</div>
                                        <div class="text-xs text-gray-400">{{ $row['date']->isToday() ? 'Hari ini' : $row['date']->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $row['package_name'] }}</td>
                                <td class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">{{ $row['capacity'] }}</td>
                                <td class="px-3 py-2 text-center font-bold text-gray-900 dark:text-gray-100">{{ $row['booked'] }}</td>
                                <td class="px-3 py-2 text-center font-bold {{ $availableColor }}">{{ $row['available'] }}</td>
                                <td class="px-6 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden min-w-[80px]">
                                            <div class="h-full {{ $barColor }}" style="width: {{ min(100, $util) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 tabular-nums w-10 text-right">{{ $util }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
