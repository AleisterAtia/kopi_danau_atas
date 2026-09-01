<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan {{ $from->format('d M Y') }} - {{ $until->format('d M Y') }}</title>
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

        .header {
            margin-bottom: 20px;
        }

        .brand-name {
            font-size: 24px;
            font-weight: bold;
            color: #2d5016;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 4px 0 0 0;
        }

        .report-period {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 25px 0 12px 0;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .report-table thead th {
            border-top: 2px solid #e5e7eb;
            border-bottom: 2px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-table tbody td {
            padding: 6px 12px;
            font-size: 11px;
            color: #4b5563;
            border-bottom: 1px solid #f3f4f6;
        }

        .report-table tfoot td {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            border-top: 2px solid #e5e7eb;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand-name">Kopi Danau Diatas</p>
        <p class="report-title">Laporan Pendapatan &amp; Okupansi</p>
        <p class="report-period">Periode: {{ $from->format('d F Y') }} &ndash; {{ $until->format('d F Y') }}</p>
    </div>

    <div class="section-title">Pendapatan per Hari</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Paket</th>
                <th>Pendapatan</th>
                <th>Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($revenue as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $row->package_name }}</td>
                    <td>Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    <td>{{ $row->transaksi }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($revenue->isNotEmpty())
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td></td>
                    <td>Rp {{ number_format($revenue->sum('total'), 0, ',', '.') }}</td>
                    <td>{{ $revenue->sum('transaksi') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="section-title">Okupansi per Paket</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Paket</th>
                <th>Total Tamu</th>
                <th>Total Booking</th>
            </tr>
        </thead>
        <tbody>
            @forelse($occupancy as $row)
                <tr>
                    <td>{{ $row->tourPackage?->name ?? '—' }}</td>
                    <td>{{ $row->total_guests }}</td>
                    <td>{{ $row->total_booking }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($occupancy->isNotEmpty())
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{{ $occupancy->sum('total_guests') }}</td>
                    <td>{{ $occupancy->sum('total_booking') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem pada {{ now()->format('d F Y, H:i') }} WIB.</p>
    </div>
</body>
</html>
