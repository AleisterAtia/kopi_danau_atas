<x-mail::message>
{{ __('mailer.booking_confirmation_greeting', ['name' => $booking->guest_name ?? $booking->user->name]) }}

{!! __('mailer.booking_confirmation_intro', ['package' => $booking->tourPackage->name]) !!}

<x-mail::panel>
**{{ __('mailer.eticket_title') }}**

{{ __('mailer.eticket_scan_instruction') }}

<div style="text-align: center; padding: 16px 0;">
<img src="{{ $message->embed(app(\App\Services\QrCodeService::class)->absolutePath($booking)) }}" alt="QR Code" width="200" height="200" style="display:inline-block;">
</div>

<div style="text-align: center; font-family: monospace; font-size: 16px; font-weight: bold; letter-spacing: 1px;">
{{ $booking->booking_code }}
</div>
</x-mail::panel>

## {{ __('mailer.detail_title') }}

| | |
|:-----|:-----|
| **{{ __('mailer.detail_booking_code') }}** | `{{ $booking->booking_code }}` |
| **{{ __('mailer.detail_package') }}** | {{ $booking->tourPackage->name }} |
| **{{ __('mailer.detail_visit_date') }}** | {{ \Carbon\Carbon::parse($booking->visit_date)->translatedFormat('l, d F Y') }} |
| **{{ __('mailer.detail_guest_count') }}** | {{ __('mailer.detail_guest_count_unit', ['count' => $booking->guest_count]) }} |
| **{{ __('mailer.detail_total') }}** | Rp {{ number_format($booking->total_price, 0, ',', '.') }} |

@if($booking->guest_name || $booking->guest_phone || $booking->notes)
## {{ __('mailer.guest_section_title') }}

| | |
|:-----|:-----|
@if($booking->guest_name)
| **{{ __('mailer.guest_name') }}** | {{ $booking->guest_name }} |
@endif
@if($booking->guest_phone)
| **{{ __('mailer.guest_phone') }}** | {{ $booking->guest_phone }} |
@endif
@if($booking->guest_email)
| **{{ __('mailer.guest_email') }}** | {{ $booking->guest_email }} |
@endif
@if($booking->notes)
| **{{ __('mailer.guest_notes') }}** | {{ $booking->notes }} |
@endif
@endif

<x-mail::button :url="route('booking.show', $booking)">
{{ __('mailer.view_details_button') }}
</x-mail::button>

> 📎 {{ __('mailer.attachment_note') }}

**{{ __('mailer.reminder_title') }}:**
- {{ __('mailer.reminder_arrive_on_time') }}
- {{ __('mailer.reminder_bring_eticket') }}
- {{ __('mailer.reminder_contact_us') }}@if(!empty($settings['company_whatsapp'])) ({{ $settings['company_whatsapp'] }})@endif

{{ __('mailer.salutation_thanks') }}<br>
**{{ __('mailer.salutation_team') }}**

<x-slot:subcopy>
{{ __('mailer.footer_auto_message') }}
</x-slot:subcopy>
</x-mail::message>
