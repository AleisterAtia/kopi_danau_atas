{{-- Poll response for the detail page: both regions, so JS can pluck each
     out by id and update the matching live container. --}}
<div id="booking-status-badge">@include('pages.booking._status_badge', ['booking' => $booking])</div>
<div id="booking-detail" class="space-y-8">@include('pages.booking._detail', ['booking' => $booking])</div>
