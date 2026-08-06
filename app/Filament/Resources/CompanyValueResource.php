<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyValueResource\Pages;
use App\Models\CompanyValue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyValueResource extends Resource
{
    use Translatable;

    protected static ?string $model = CompanyValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Nilai Kami';

    /** Curated so admins pick a valid blade-heroicons outline name, not free text. */
    public const ICONS = [
        'flag' => 'Flag',
        'book-open' => 'Book Open',
        'user-group' => 'User Group',
        'globe-alt' => 'Globe',
        'sparkles' => 'Sparkles',
        'heart' => 'Heart',
        'shield-check' => 'Shield Check',
        'sun' => 'Sun',
        'academic-cap' => 'Academic Cap',
        'trophy' => 'Trophy',
        'hand-raised' => 'Hand Raised',
        'star' => 'Star',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('icon')
                ->options(self::ICONS)
                ->required()
                ->default('sparkles'),

            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')->badge(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => Pages\ListCompanyValues::route('/'),
            'create' => Pages\CreateCompanyValue::route('/create'),
            'edit' => Pages\EditCompanyValue::route('/{record}/edit'),
        ];
    }
}
