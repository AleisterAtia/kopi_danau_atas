<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->integer('guest_count');
            $table->decimal('total_price', 12, 2);
            $table->enum('status', [
                'pending', 'paid', 'confirmed', 'completed', 'cancelled', 'expired',
            ])->default('pending');
            $table->string('qr_code_path')->nullable();
            $table->timestamps();

            $table->index(['tour_package_id', 'visit_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
