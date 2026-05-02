<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('midtrans_order_id', 50)->unique();
            $table->string('midtrans_transaction_id', 50)->nullable();
            $table->string('snap_token')->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('gross_amount', 12, 2);
            $table->json('midtrans_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
