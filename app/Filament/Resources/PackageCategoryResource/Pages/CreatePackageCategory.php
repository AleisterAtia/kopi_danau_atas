<?php

namespace App\Filament\Resources\PackageCategoryResource\Pages;

use App\Filament\Resources\PackageCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreatePackageCategory extends CreateRecord
{
    use Translatable;

    protected static string $resource = PackageCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\LocaleSwitcher::make()];
    }
}
