<?php
namespace App\Filament\Resources\CoffeeVarietyResource\Pages;
use App\Filament\Resources\CoffeeVarietyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListCoffeeVarieties extends ListRecords
{
    protected static string $resource = CoffeeVarietyResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
