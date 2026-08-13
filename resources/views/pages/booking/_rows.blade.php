@foreach($bookings as $booking)
<tr class="hover:bg-bg-warm transition-colors">
    <td class="py-4 px-6">
        <div class="font-mono text-sm text-gray-500 mb-1">{{ $booking->booking_code }}</div>
        <div class="font-bold text-gray-900">{{ $booking->tourPackage->name }}</div>
        <div class="text-sm text-gray-500">{{ $booking->guest_count }} {{ __('Orang') }}</div>
    </td>
    <td class="py-4 px-6">
        <div class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }}</div>
        <div class="text-xs text-gray-500">{{ __('Dipesan') }}: {{ $booking->created_at->format('d/m/Y') }}</div>
    </td>
    <td class="py-4 px-6">
        <div class="font-bold text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
    </td>
    <td class="py-4 px-6">
        @if($booking->status == 'pending')
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Menunggu Pembayaran') }}</span>
        @elseif($booking->status == 'paid')
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Lunas') }}</span>
        @elseif($booking->status == 'confirmed')
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('Terkonfirmasi') }}</span>
        @elseif($booking->status == 'completed')
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Selesai') }}</span>
        @elseif($booking->status == 'cancelled')
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('Dibatalkan') }}</span>
        @elseif($booking->status == 'expired')
            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-700">{{ __('Kadaluarsa') }}</span>
        @endif
    </td>
    <td class="py-4 px-6 text-right space-x-2">
        @if($booking->status == 'pending')
            <a href="{{ route('payment.checkout', $booking) }}" class="inline-flex items-center text-sm bg-primary text-white px-3 py-1.5 rounded hover:bg-primary-light transition-colors">
                {{ __('Bayar') }}
            </a>
        @endif
        @if(in_array($booking->status, ['paid', 'confirmed', 'completed']))
            <a href="{{ route('booking.invoice', $booking) }}" class="inline-flex items-center text-sm border border-green-300 bg-green-50 text-green-700 px-3 py-1.5 rounded hover:bg-green-100 transition-colors" title="{{ __('Download Invoice') }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('Invoice') }}
            </a>
        @endif
        <a href="{{ route('booking.show', $booking) }}" class="inline-flex items-center text-sm border border-border bg-white text-text-secondary px-3 py-1.5 rounded-lg hover:bg-bg-warm transition-colors">
            {{ __('Detail') }}
        </a>
    </td>
</tr>
@endforeach
