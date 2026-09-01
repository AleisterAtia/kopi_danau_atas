<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshExchangeRate extends Command
{
    protected $signature = 'exchange-rate:refresh';

    protected $description = 'Fetch the latest USD->IDR rate for USD price display';

    public function handle(): int
    {
        try {
            $response = Http::timeout(10)->get('https://api.frankfurter.app/latest', [
                'from' => 'USD',
                'to' => 'IDR',
            ]);

            $rate = $response->json('rates.IDR');

            if ($response->failed() || ! is_numeric($rate)) {
                $this->warn('Exchange rate fetch failed, keeping last known rate.');

                return self::FAILURE;
            }

            SiteSetting::setValue('usd_idr_rate', (string) $rate);
            $this->info("Updated USD/IDR rate to {$rate}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::warning('Exchange rate refresh exception: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
