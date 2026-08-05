<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan';

    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'dateFrom' => now()->startOfMonth()->toDateString(),
            'dateUntil' => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('dateFrom')->label('Dari Tanggal')->live()->required(),
                DatePicker::make('dateUntil')->label('Sampai Tanggal')->live()->required(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->exportPdf()),
            Action::make('exportCsv')
                ->label('Unduh CSV')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    protected function periodFrom(): Carbon
    {
        return Carbon::parse($this->data['dateFrom'] ?? now()->startOfMonth())->startOfDay();
    }

    protected function periodUntil(): Carbon
    {
        return Carbon::parse($this->data['dateUntil'] ?? now())->endOfDay();
    }

    public function revenueRows(): Collection
    {
        return Payment::where('status', 'settlement')
            ->whereBetween('paid_at', [$this->periodFrom(), $this->periodUntil()])
            ->selectRaw('DATE(paid_at) as date, SUM(gross_amount) as total, COUNT(*) as transaksi')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function occupancyRows(): Collection
    {
        return Booking::whereIn('status', ['paid', 'confirmed', 'completed'])
            ->whereBetween('visit_date', [$this->periodFrom(), $this->periodUntil()])
            ->with('tourPackage:id,name')
            ->selectRaw('tour_package_id, SUM(guest_count) as total_guests, COUNT(*) as total_booking')
            ->groupBy('tour_package_id')
            ->orderByDesc('total_guests')
            ->get();
    }

    protected function exportPdf()
    {
        $pdf = Pdf::loadView('pdf.report', [
            'revenue' => $this->revenueRows(),
            'occupancy' => $this->occupancyRows(),
            'from' => $this->periodFrom(),
            'until' => $this->periodUntil(),
        ])->setPaper('a4', 'portrait');

        return Response::streamDownload(
            fn () => print ($pdf->output()),
            "laporan-{$this->periodFrom()->toDateString()}-{$this->periodUntil()->toDateString()}.pdf"
        );
    }

    protected function exportCsv()
    {
        $revenue = $this->revenueRows();
        $occupancy = $this->occupancyRows();

        return Response::streamDownload(function () use ($revenue, $occupancy) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Laporan Pendapatan']);
            fputcsv($out, ['Tanggal', 'Total Pendapatan', 'Jumlah Transaksi']);
            foreach ($revenue as $row) {
                fputcsv($out, [$row->date, $row->total, $row->transaksi]);
            }

            fputcsv($out, []);

            fputcsv($out, ['Laporan Okupansi Paket']);
            fputcsv($out, ['Paket', 'Total Tamu', 'Total Booking']);
            foreach ($occupancy as $row) {
                fputcsv($out, [$row->tourPackage?->name ?? '—', $row->total_guests, $row->total_booking]);
            }

            fclose($out);
        }, "laporan-{$this->periodFrom()->toDateString()}-{$this->periodUntil()->toDateString()}.csv");
    }
}
