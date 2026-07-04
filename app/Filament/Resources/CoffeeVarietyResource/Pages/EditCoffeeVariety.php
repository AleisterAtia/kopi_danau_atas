<?php

namespace App\Filament\Resources\CoffeeVarietyResource\Pages;

use App\Filament\Resources\CoffeeVarietyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditCoffeeVariety extends EditRecord
{
    use Translatable;

    protected static string $resource = CoffeeVarietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
