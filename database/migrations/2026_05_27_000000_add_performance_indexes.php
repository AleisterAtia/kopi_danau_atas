<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes added in Sprint 2.
 *
 * - `bookings(visit_date, status)`: speeds up the daily expire/auto-complete
 *   schedulers and the per-package quota query (`whereDate(visit_date,...)`
 *   joined with status filtering).
 * - `bookings(user_id, created_at desc)`: speeds up the "My Bookings"
 *   listing (`where user_id ... orderByDesc(created_at)`).
 * - `reviews(tour_package_id)`: backs the avg/count aggregates on
 *   tour package detail/listing pages.
 *
 * `payments(midtrans_order_id)` already exists as UNIQUE.
 * `bookings(tour_package_id, visit_date, status)` already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Some MySQL versions don't allow conditional index creation,
            // so we use named indexes that are safe to drop in down().
            $table->index(['visit_date', 'status'], 'bookings_visit_date_status_idx');
            $table->index(['user_id', 'created_at'], 'bookings_user_id_created_at_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('tour_package_id', 'reviews_tour_package_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_visit_date_status_idx');
            $table->dropIndex('bookings_user_id_created_at_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_tour_package_id_idx');
        });
    }
};
