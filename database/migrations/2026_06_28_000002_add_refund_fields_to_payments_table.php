<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Audit trail for admin-initiated refunds. The actual money
            // movement is performed in the Midtrans dashboard; these columns
            // record that decision so application state never silently drifts
            // from the gateway.
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->string('refund_note', 500)->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['refunded_at', 'refund_note']);
        });
    }
};
