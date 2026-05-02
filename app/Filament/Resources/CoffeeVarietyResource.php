<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoffeeVarietyResource\Pages;
use App\Models\CoffeeVariety;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoffeeVarietyResource extends Resource
{
    protected static ?string $model = CoffeeVariety::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Coffee Varieties';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Coffee Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('slug')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('origin')
                        ->maxLength(255)
                        ->placeholder('e.g., Alahan Panjang, Solok'),

                    Forms\Components\FileUpload::make('image_path')
                        ->label('Image')
                        ->image()
                        ->directory('coffee')
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('4:3')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('600'),

                    Forms\Components\RichEditor::make('description')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('flavor_profile')
                        ->label('Flavor Profile')
                        ->placeholder('e.g., Chocolatey, Fruity, Low Acidity...')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->width(60)
                    ->height(45),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('origin')
                    ->sortable(),

                Tables\Columns\TextColumn::make('flavor_profile')
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListCoffeeVarieties::route('/'),
            'create' => Pages\CreateCoffeeVariety::route('/create'),
            'edit' => Pages\EditCoffeeVariety::route('/{record}/edit'),
        ];
    }
}
