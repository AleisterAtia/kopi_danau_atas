<?php

namespace App\Filament\Resources\CompanyValueResource\Pages;

use App\Filament\Resources\CompanyValueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateCompanyValue extends CreateRecord
{
    use Translatable;

    protected static string $resource = CompanyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\LocaleSwitcher::make()];
    }
}
