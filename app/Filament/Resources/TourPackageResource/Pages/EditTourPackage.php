<?php

namespace App\Filament\Resources\TourPackageResource\Pages;

use App\Filament\Resources\TourPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditTourPackage extends EditRecord
{
    use Translatable;

    protected static string $resource = TourPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
