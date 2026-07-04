<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make coffee_varieties bilingual, mirroring the earlier
 * make_content_columns_translatable migration. `name` stays a plain string
 * (variety names — Lini-S, Gayo, Typica — are proper nouns, and HasSlug
 * generates the slug from it). English is filled afterwards by Gemini via
 * `php artisan content:translate` (these strings were never in lang/en.json).
 */
return new class extends Migration
{
    private array $columns = ['origin', 'description', 'flavor_profile'];

    /** VARCHAR(255) columns widened before wrapping so the JSON envelope can't truncate. */
    private array $widen = ['origin'];

    public function up(): void
    {
        Schema::table('coffee_varieties', function (Blueprint $t) {
            foreach ($this->widen as $c) {
                $t->text($c)->nullable()->change();
            }
        });

        foreach ($this->columns as $c) {
            DB::table('coffee_varieties')->whereNotNull($c)->update([
                $c => DB::raw("json_object('id', `{$c}`)"),
            ]);
        }

        Schema::table('coffee_varieties', function (Blueprint $t) {
            foreach ($this->columns as $c) {
                $t->json($c)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coffee_varieties', function (Blueprint $t) {
            foreach ($this->columns as $c) {
                $t->text($c)->nullable()->change();
            }
        });

        foreach ($this->columns as $c) {
            DB::table('coffee_varieties')->whereNotNull($c)->update([
                $c => DB::raw("json_unquote(json_extract(`{$c}`, '\$.id'))"),
            ]);
        }
    }
};
