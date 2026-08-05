<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php
        $revenue = $this->revenueRows();
        $occupancy = $this->occupancyRows();
        $revenueTotal = $revenue->sum('total');
        $guestTotal = $occupancy->sum('total_guests');
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-filament::section heading="Pendapatan per Hari">
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="py-2">Tanggal</th>
                            <th class="py-2">Pendapatan</th>
                            <th class="py-2">Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenue as $row)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2">{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}</td>
                                <td class="py-2">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                <td class="py-2">{{ $row->transaksi }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">Tidak ada data pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($revenue->isNotEmpty())
                        <tfoot>
                            <tr class="border-t border-gray-200 font-semibold dark:border-white/10">
                                <td class="py-2">Total</td>
                                <td class="py-2">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</td>
                                <td class="py-2">{{ $revenue->sum('transaksi') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>

        <x-filament::section heading="Okupansi per Paket">
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="py-2">Paket</th>
                            <th class="py-2">Total Tamu</th>
                            <th class="py-2">Total Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($occupancy as $row)
                            <tr class="border-t border-gray-100 dark:border-white/5">
                                <td class="py-2">{{ $row->tourPackage?->name ?? '—' }}</td>
                                <td class="py-2">{{ $row->total_guests }}</td>
                                <td class="py-2">{{ $row->total_booking }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">Tidak ada data pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($occupancy->isNotEmpty())
                        <tfoot>
                            <tr class="border-t border-gray-200 font-semibold dark:border-white/10">
                                <td class="py-2">Total</td>
                                <td class="py-2">{{ $guestTotal }}</td>
                                <td class="py-2">{{ $occupancy->sum('total_booking') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
