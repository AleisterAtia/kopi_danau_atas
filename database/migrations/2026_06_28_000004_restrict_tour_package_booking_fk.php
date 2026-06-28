<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change bookings.tour_package_id from CASCADE to RESTRICT on delete so a
     * tour package that still has bookings cannot be hard-deleted — which
     * would otherwise wipe the financial history (bookings + payments).
     *
     * SQLite (used by the test suite) cannot alter a foreign key in place, so
     * this is skipped there; the same rule is enforced application-side by the
     * `deleting` guard in App\Models\TourPackage, which is DB-agnostic.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['tour_package_id']);
            $table->foreign('tour_package_id')
                ->references('id')->on('tour_packages')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['tour_package_id']);
            $table->foreign('tour_package_id')
                ->references('id')->on('tour_packages')
                ->cascadeOnDelete();
        });
    }
};
