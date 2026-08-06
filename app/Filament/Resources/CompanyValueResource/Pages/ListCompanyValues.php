<?php

namespace App\Filament\Resources\CompanyValueResource\Pages;

use App\Filament\Resources\CompanyValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;

class ListCompanyValues extends ListRecords
{
    use Translatable;

    protected static string $resource = CompanyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\LocaleSwitcher::make(), Actions\CreateAction::make()];
    }
}
