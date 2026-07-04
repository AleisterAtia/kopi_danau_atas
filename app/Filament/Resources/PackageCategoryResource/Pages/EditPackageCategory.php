<?php

namespace App\Filament\Resources\PackageCategoryResource\Pages;

use App\Filament\Resources\PackageCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPackageCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = PackageCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\LocaleSwitcher::make(), Actions\DeleteAction::make()];
    }
}
