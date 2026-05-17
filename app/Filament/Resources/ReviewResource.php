<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Tourism';
    protected static ?int $navigationSort = 3;

    /**
     * Reviews are published immediately when submitted by users to keep the
     * platform transparent — admins cannot hide low ratings. The form below
     * is read-only and exists only so admins can inspect a review's content.
     * The `status` column is intentionally omitted from the UI; it remains
     * in the database (always 'approved') for backward compatibility.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Review Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->disabled(),
                    Forms\Components\Select::make('tour_package_id')
                        ->relationship('tourPackage', 'name')
                        ->disabled(),
                    Forms\Components\TextInput::make('rating')
                        ->disabled(),
                    Forms\Components\Textarea::make('comment')
                        ->disabled()
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tourPackage.name')
                    ->label('Package')
                    ->sortable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),

                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // View-only edit action (form fields are disabled). Delete is
                // intentionally retained so admins can remove spam or
                // offensive content (e.g., hate speech), but it is the only
                // intervention available — no approve/reject moderation.
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
