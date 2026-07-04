<?php

namespace App\Filament\Resources\CoffeeVarietyResource\Pages;

use App\Filament\Resources\CoffeeVarietyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateCoffeeVariety extends CreateRecord
{
    use Translatable;

    protected static string $resource = CoffeeVarietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
