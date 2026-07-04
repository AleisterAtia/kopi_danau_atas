<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\HomepageSection;
use App\Models\PackageCategory;
use App\Models\TourPackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns that become spatie/laravel-translatable JSON: {"id": "...", "en": "..."}.
     */
    private array $columns = [
        'tour_packages' => ['name', 'description', 'facilities'],
        'blog_posts' => ['title', 'content', 'meta_title', 'meta_description'],
        'blog_categories' => ['name'],
        'package_categories' => ['name'],
        'homepage_sections' => ['title', 'description'],
    ];

    /**
     * VARCHAR(255) columns must be widened before wrapping, otherwise the
     * added JSON envelope ({"id": "..."}) could truncate a long value.
     */
    private array $widen = [
        'tour_packages' => ['name'],
        'blog_posts' => ['title', 'meta_title'],
        'blog_categories' => ['name'],
        'package_categories' => ['name'],
        'homepage_sections' => ['title'],
    ];

    public function up(): void
    {
        foreach ($this->widen as $table => $cols) {
            Schema::table($table, function (Blueprint $t) use ($cols) {
                foreach ($cols as $c) {
                    $t->text($c)->nullable()->change();
                }
            });
        }

        // Wrap each existing plain value into {"id": "<value>"} so the column
        // holds valid JSON. Existing rows are Indonesian-only; English is
        // filled below from lang/en.json where a match exists, otherwise by an
        // admin via the Filament locale switcher.
        foreach ($this->columns as $table => $cols) {
            foreach ($cols as $c) {
                DB::table($table)->whereNotNull($c)->update([
                    $c => DB::raw("json_object('id', `{$c}`)"),
                ]);
            }
        }

        foreach ($this->columns as $table => $cols) {
            Schema::table($table, function (Blueprint $t) use ($cols) {
                foreach ($cols as $c) {
                    $t->json($c)->nullable()->change();
                }
            });
        }

        $this->backfillEnglishFromLangFile();
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $cols) {
            Schema::table($table, function (Blueprint $t) use ($cols) {
                foreach ($cols as $c) {
                    $t->text($c)->nullable()->change();
                }
            });

            // Unwrap back to the Indonesian value. ponytail: restores to TEXT,
            // not the original VARCHAR/longText mix — fine for a dev rollback.
            foreach ($cols as $c) {
                DB::table($table)->whereNotNull($c)->update([
                    $c => DB::raw("json_unquote(json_extract(`{$c}`, '\$.id'))"),
                ]);
            }
        }
    }

    /**
     * Reuse the English strings already sitting in lang/en.json (keyed by the
     * Indonesian source string) so admins don't retype content that's already
     * translated — packages and homepage sections match; blog content does not
     * (it was never in en.json) and stays Indonesian until translated.
     */
    private function backfillEnglishFromLangFile(): void
    {
        $path = lang_path('en.json');
        if (! is_file($path)) {
            return;
        }

        $en = json_decode((string) file_get_contents($path), true) ?: [];
        if (! $en) {
            return;
        }

        $apply = function ($records, array $fields) use ($en) {
            foreach ($records as $record) {
                $dirty = false;
                foreach ($fields as $field) {
                    $id = $record->getTranslation($field, 'id', false);
                    if (is_string($id) && $id !== '' && isset($en[$id]) && ! $record->hasTranslation($field, 'en')) {
                        $record->setTranslation($field, 'en', $en[$id]);
                        $dirty = true;
                    }
                }
                if ($dirty) {
                    $record->saveQuietly(); // no slug regen / observers
                }
            }
        };

        $apply(TourPackage::all(), ['name', 'description', 'facilities']);
        $apply(HomepageSection::all(), ['title', 'description']);
        $apply(BlogPost::all(), ['title', 'content', 'meta_title', 'meta_description']);
        $apply(BlogCategory::all(), ['name']);
        $apply(PackageCategory::all(), ['name']);
    }
};
