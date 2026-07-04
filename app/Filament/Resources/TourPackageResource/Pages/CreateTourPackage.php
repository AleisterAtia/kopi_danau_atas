<?php

namespace App\Filament\Resources\TourPackageResource\Pages;

use App\Filament\Resources\TourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateTourPackage extends CreateRecord
{
    use Translatable;

    protected static string $resource = TourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
