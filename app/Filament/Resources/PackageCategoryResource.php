<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageCategoryResource\Pages;
use App\Models\PackageCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PackageCategoryResource extends Resource
{
    use Translatable;

    protected static ?string $model = PackageCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Tourism';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Package Categories';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true),

            Forms\Components\TextInput::make('slug')
                ->disabled()
                ->dehydrated()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('packages_count')
                    ->counts('packages')
                    ->label('Packages'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackageCategories::route('/'),
            'create' => Pages\CreatePackageCategory::route('/create'),
            'edit' => Pages\EditPackageCategory::route('/{record}/edit'),
        ];
    }
}
