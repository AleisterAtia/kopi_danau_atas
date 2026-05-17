<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $booking->booking_code }}</title>
    <style>
        @page {
            margin: 40px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            margin-bottom: 30px;
            overflow: hidden;
        }

        .header-left {
            float: left;
            width: 50%;
        }

        .header-right {
            float: right;
            width: 50%;
            text-align: right;
        }

        .brand-name {
            font-size: 24px;
            font-weight: bold;
            color: #2d5016;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 0 0 12px 0;
        }

        .paid-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 4px;
            border: 1px solid #bbf7d0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Invoice Meta */
        .meta-section {
            margin-bottom: 25px;
        }

        .meta-label {
            color: #9ca3af;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
        }

        /* Address Section */
        .address-section {
            margin-bottom: 30px;
            overflow: hidden;
        }

        .address-from {
            float: left;
            width: 48%;
        }

        .address-to {
            float: right;
            width: 48%;
        }

        .address-heading {
            font-size: 10px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .address-name {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .address-detail {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }

        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 6px;
        }

        /* Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-table thead th {
            border-top: 2px solid #e5e7eb;
            border-bottom: 2px solid #e5e7eb;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .invoice-table thead th:last-child {
            text-align: right;
        }

        .invoice-table tbody td {
            padding: 8px 12px;
            font-size: 11px;
            color: #4b5563;
            vertical-align: top;
        }

        .invoice-table tbody td:last-child {
            text-align: right;
        }

        .package-name {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .item-row td {
            padding: 6px 12px;
        }

        .item-bullet {
            color: #2d5016;
            margin-right: 4px;
        }

        /* Totals */
        .totals-section {
            width: 100%;
            margin-top: 10px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 12px;
            font-size: 12px;
        }

        .totals-table .label {
            text-align: right;
            color: #6b7280;
            font-weight: 600;
            width: 70%;
        }

        .totals-table .value {
            text-align: right;
            color: #1f2937;
            width: 30%;
        }

        .totals-table .total-row td {
            padding-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .totals-table .total-row .label {
            color: #1f2937;
        }

        .totals-table .total-row .value {
            color: #1f2937;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 0;
        }

        /* Payment Info */
        .payment-info {
            margin-top: 30px;
            padding: 16px;
            background-color: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .payment-info-title {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .payment-info-row {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .payment-info-row strong {
            color: #374151;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* E-Ticket QR */
        .eticket-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f0fdf4;
            border: 1px dashed #86efac;
            border-radius: 8px;
            overflow: hidden;
        }

        .eticket-left {
            float: left;
            width: 35%;
            text-align: center;
        }

        .eticket-right {
            float: right;
            width: 60%;
        }

        .eticket-qr {
            width: 140px;
            height: 140px;
        }

        .eticket-title {
            font-size: 13px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .eticket-code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            padding: 8px 12px;
            background-color: #ffffff;
            border-radius: 4px;
            display: inline-block;
            border: 1px solid #d1fae5;
            margin-top: 8px;
        }

        .eticket-instruction {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header clearfix">
        <div class="header-left">
            <p class="brand-name">Kopi Danau Atas</p>
        </div>
        <div class="header-right">
            <p class="invoice-title">Invoice</p>
            <span class="paid-badge">PAID</span>
        </div>
    </div>

    {{-- INVOICE META --}}
    <div class="meta-section">
        <div class="meta-label">No. Invoice</div>
        <div class="meta-value">{{ $booking->booking_code }}</div>
        <div style="margin-top: 8px;">
            <div class="meta-label">Tanggal</div>
            <div class="meta-value">{{ \Carbon\Carbon::parse($booking->payment->paid_at ?? $booking->created_at)->format('d F Y') }}</div>
        </div>
    </div>

    {{-- ADDRESSES --}}
    <div class="address-section clearfix">
        <div class="address-from">
            <div class="address-heading">Dari:</div>
            <div class="address-name">CV. Kopi Danau Atas</div>
            <div class="address-detail">
                Jl. Raya Situ Patenggang,<br>
                Ciwidey, Kab. Bandung,<br>
                Jawa Barat 40973
            </div>
        </div>
        <div class="address-to">
            <div class="address-heading">Kepada:</div>
            <div class="address-name">{{ $booking->user->name }}</div>
            <div class="address-detail">
                @if($booking->user->phone)
                    {{ $booking->user->phone }}<br>
                @endif
                {{ $booking->user->email }}
            </div>
        </div>
    </div>

    {{-- SECTION TITLE --}}
    <div class="section-title">Rincian Pembayaran</div>

    {{-- ITEMS TABLE --}}
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="package-name">{{ $booking->tourPackage->name }}</div>
                </td>
                <td></td>
            </tr>
            <tr class="item-row">
                <td>
                    <span class="item-bullet">▸</span>
                    {{ \Carbon\Carbon::parse($booking->visit_date)->translatedFormat('l, d F Y') }}
                    &middot; {{ $booking->guest_count }} Orang
                    &middot; {{ $booking->tourPackage->duration_hours }} Jam
                </td>
                <td>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <hr class="divider">

    {{-- TOTALS --}}
    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td class="label">Total Pembayaran</td>
            <td class="value">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- E-TICKET QR --}}
    @if($booking->qr_code_path && file_exists(storage_path('app/public/' . $booking->qr_code_path)))
    <div class="eticket-section clearfix">
        <div class="eticket-left">
            <img src="{{ storage_path('app/public/' . $booking->qr_code_path) }}" alt="QR" class="eticket-qr">
        </div>
        <div class="eticket-right">
            <div class="eticket-title">E-Tiket Kunjungan</div>
            <div class="eticket-instruction">
                Tunjukkan QR code ini kepada petugas saat tiba di lokasi untuk verifikasi pemesanan Anda.
            </div>
            <div class="eticket-code">{{ $booking->booking_code }}</div>
        </div>
    </div>
    @endif

    {{-- PAYMENT INFO --}}
    @if($booking->payment)
    <div class="payment-info">
        <div class="payment-info-title">Informasi Pembayaran</div>
        @if($booking->payment->payment_type)
            <div class="payment-info-row">
                <strong>Metode:</strong> {{ strtoupper(str_replace('_', ' ', $booking->payment->payment_type)) }}
            </div>
        @endif
        @if($booking->payment->midtrans_order_id)
            <div class="payment-info-row">
                <strong>ID Transaksi:</strong> {{ $booking->payment->midtrans_order_id }}
            </div>
        @endif
        @if($booking->payment->paid_at)
            <div class="payment-info-row">
                <strong>Dibayar Pada:</strong> {{ \Carbon\Carbon::parse($booking->payment->paid_at)->format('d F Y, H:i') }} WIB
            </div>
        @endif
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Terima kasih telah memesan paket wisata di Kopi Danau Atas.</p>
        <p>Invoice ini digenerate secara otomatis dan sah tanpa tanda tangan.</p>
    </div>
</body>
</html>
