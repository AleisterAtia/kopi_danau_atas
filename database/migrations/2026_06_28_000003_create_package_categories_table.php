<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Optional category so the public catalogue can be filtered.
        Schema::table('tour_packages', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                // SQLite can't add a foreign key to an existing table via ALTER;
                // the plain column is enough for the relationship & the tests.
                $table->unsignedBigInteger('category_id')->nullable()->after('slug');
            } else {
                // nullOnDelete: removing a category leaves its packages intact
                // (just uncategorised) rather than cascading.
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('slug')
                    ->constrained('package_categories')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn('category_id');
            } else {
                $table->dropConstrainedForeignId('category_id');
            }
        });

        Schema::dropIfExists('package_categories');
    }
};
