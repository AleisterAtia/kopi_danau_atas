<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TourPackageResource\Pages;
use App\Models\TourPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TourPackageResource extends Resource
{
    use Translatable;

    protected static ?string $model = TourPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Tourism';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Package Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('slug')
                        ->disabled()
                        ->dehydrated()
                        ->maxLength(255),

                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ]),

                    Forms\Components\RichEditor::make('description')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->minValue(0),

                    Forms\Components\TextInput::make('duration_hours')
                        ->label('Duration (hours)')
                        ->required()
                        ->numeric()
                        ->minValue(1),

                    Forms\Components\TextInput::make('daily_capacity')
                        ->label('Daily Capacity (persons)')
                        ->required()
                        ->numeric()
                        ->minValue(1),

                    Forms\Components\Textarea::make('facilities')
                        ->placeholder('List facilities, one per line')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Images')
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->relationship()
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->image()
                                ->directory('packages')
                                ->required()
                                ->maxSize(5120)
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('16:9')
                                ->imageResizeTargetWidth('1200')
                                ->imageResizeTargetHeight('675'),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible(),
                ]),

            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured on Homepage')
                        ->default(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_hours')
                    ->label('Duration')
                    ->suffix(' hrs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('daily_capacity')
                    ->label('Capacity')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    // Block deleting a package that has bookings (would wipe
                    // financial history); the model guard is the real safety
                    // net — this just gives a clean message instead of an error.
                    ->before(function (TourPackage $record, Tables\Actions\DeleteAction $action) {
                        if ($record->bookings()->exists()) {
                            Notification::make()
                                ->title('Paket tidak dapat dihapus')
                                ->body('Paket ini masih memiliki booking. Nonaktifkan saja (matikan "Active") untuk menyembunyikannya.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        // Skip packages that have bookings; delete the rest.
                        ->action(function (Collection $records) {
                            $blocked = $records->filter(fn (TourPackage $r) => $r->bookings()->exists());
                            $deletable = $records->reject(fn (TourPackage $r) => $r->bookings()->exists());

                            $deletable->each->delete();

                            if ($deletable->isNotEmpty()) {
                                Notification::make()
                                    ->title($deletable->count().' paket dihapus')
                                    ->success()
                                    ->send();
                            }

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->title($blocked->count().' paket dilewati')
                                    ->body('Paket yang memiliki booking tidak dihapus demi menjaga riwayat finansial.')
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTourPackages::route('/'),
            'create' => Pages\CreateTourPackage::route('/create'),
            'edit' => Pages\EditTourPackage::route('/{record}/edit'),
        ];
    }
}
