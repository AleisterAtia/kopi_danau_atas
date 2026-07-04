<?php

namespace App\Console\Commands;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CoffeeVariety;
use App\Models\HomepageSection;
use App\Models\PackageCategory;
use App\Models\TourPackage;
use App\Services\GeminiTranslator;
use Illuminate\Console\Command;

class TranslateContent extends Command
{
    protected $signature = 'content:translate {--locale=en : Target locale to fill}';

    protected $description = 'Fill missing translations for existing CMS content via Gemini (idempotent — safe to re-run).';

    /** Models whose translatable fields should be backfilled. */
    private array $models = [
        TourPackage::class,
        BlogPost::class,
        BlogCategory::class,
        PackageCategory::class,
        HomepageSection::class,
        CoffeeVariety::class,
    ];

    public function handle(GeminiTranslator $translator): int
    {
        if (! $translator->enabled()) {
            $this->error('GEMINI_API_KEY is not set — nothing to do. Add it to .env first.');

            return self::FAILURE;
        }

        $locale = $this->option('locale');
        $filled = 0;

        foreach ($this->models as $model) {
            $records = $model::all();
            $this->info(class_basename($model).': '.$records->count().' record(s)');

            foreach ($records as $record) {
                if ($record->fillMissingTranslations([$locale])) {
                    $record->saveQuietly(); // skip the saving hook to avoid re-translating
                    $filled++;
                    $this->line('  ✓ '.class_basename($model).' #'.$record->getKey());
                }
            }
        }

        $this->newLine();
        $this->info("Done. Filled {$locale} for {$filled} record(s). Re-run to catch any skipped by rate limits.");

        return self::SUCCESS;
    }
}
