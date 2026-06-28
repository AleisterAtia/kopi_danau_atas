<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Unguessable per-ticket secret embedded in the QR (replaces the
            // sequential booking_code, which was trivially enumerable). Used
            // by the on-site check-in flow to look up a booking.
            $table->string('ticket_token', 64)->nullable()->unique()->after('qr_code_path');

            // When the e-ticket was scanned/validated at the venue, and by
            // which staff account. A non-null checked_in_at means "used".
            $table->timestamp('checked_in_at')->nullable()->after('ticket_token');
            $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by');
            $table->dropColumn(['ticket_token', 'checked_in_at']);
        });
    }
};
