<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_pdf_export_downloads_for_the_selected_period(): void
    {
        $this->actingAsAdmin();

        $booking = Booking::factory()->paid()->create([
            'visit_date' => now()->toDateString(),
        ]);
        Payment::factory()->settled()->create(['booking_id' => $booking->id]);

        Livewire::test(Reports::class)
            ->set('data.dateFrom', now()->startOfMonth()->toDateString())
            ->set('data.dateUntil', now()->toDateString())
            ->callAction('exportPdf')
            ->assertFileDownloaded();
    }

    public function test_csv_export_downloads_for_the_selected_period(): void
    {
        $this->actingAsAdmin();

        $booking = Booking::factory()->paid()->create([
            'visit_date' => now()->toDateString(),
        ]);
        Payment::factory()->settled()->create(['booking_id' => $booking->id]);

        Livewire::test(Reports::class)
            ->set('data.dateFrom', now()->startOfMonth()->toDateString())
            ->set('data.dateUntil', now()->toDateString())
            ->callAction('exportCsv')
            ->assertFileDownloaded();
    }

    public function test_revenue_and_occupancy_rows_are_scoped_to_the_selected_period(): void
    {
        $this->actingAsAdmin();

        $inRange = Booking::factory()->paid()->create(['visit_date' => now()->toDateString()]);
        Payment::factory()->settled()->create(['booking_id' => $inRange->id, 'gross_amount' => 250000]);

        $outOfRange = Booking::factory()->paid()->create(['visit_date' => now()->subMonths(2)->toDateString()]);
        Payment::factory()->settled()->create([
            'booking_id' => $outOfRange->id,
            'gross_amount' => 999000,
            'paid_at' => now()->subMonths(2),
        ]);

        $component = Livewire::test(Reports::class)
            ->set('data.dateFrom', now()->startOfMonth()->toDateString())
            ->set('data.dateUntil', now()->toDateString());

        $revenue = $component->instance()->revenueRows();
        $occupancy = $component->instance()->occupancyRows();

        $this->assertEquals(250000, $revenue->sum('total'));
        $this->assertEquals($inRange->guest_count, $occupancy->sum('total_guests'));
    }
}
