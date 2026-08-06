<?php

namespace App\Filament\Resources\CompanyValueResource\Pages;

use App\Filament\Resources\CompanyValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditCompanyValue extends EditRecord
{
    use Translatable;

    protected static string $resource = CompanyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\LocaleSwitcher::make(), Actions\DeleteAction::make()];
    }
}
