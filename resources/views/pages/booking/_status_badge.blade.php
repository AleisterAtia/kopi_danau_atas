@if($booking->status == 'pending')
    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">{{ __('Menunggu Pembayaran') }}</span>
@elseif($booking->status == 'paid')
    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">{{ __('Lunas') }}</span>
@elseif($booking->status == 'confirmed')
    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">{{ __('Terkonfirmasi') }}</span>
@elseif($booking->status == 'completed')
    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">{{ __('Selesai') }}</span>
@elseif($booking->status == 'cancelled')
    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800">{{ __('Dibatalkan') }}</span>
@elseif($booking->status == 'expired')
    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-200 text-gray-700">{{ __('Kadaluarsa') }}</span>
@endif
