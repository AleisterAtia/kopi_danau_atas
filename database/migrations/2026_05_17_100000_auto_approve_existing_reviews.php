<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reviews are now auto-published on submission to keep the platform
 * transparent (admins cannot hide low ratings). This migration normalizes
 * any historical reviews that were left in 'pending' or 'rejected' state
 * so they become visible to the public.
 *
 * The `status` column itself is kept on the `reviews` table for backward
 * compatibility — existing code still reads it, but it will always be
 * 'approved' going forward.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('reviews')
            ->whereIn('status', ['pending', 'rejected'])
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        // No-op: we don't restore the previous moderation state because the
        // original pending/rejected distinction is intentionally discarded.
    }
};
