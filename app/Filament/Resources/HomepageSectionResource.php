<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageSectionResource\Pages;
use App\Models\HomepageSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomepageSectionResource extends Resource
{
    use Translatable;

    protected static ?string $model = HomepageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Homepage Sections';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Section Content')
                ->schema([
                    Forms\Components\TextInput::make('section_key')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?HomepageSection $record) => $record !== null)
                        ->dehydrated()
                        ->helperText('Unique key: hero, about, education, featured_packages, etc.'),

                    Forms\Components\TextInput::make('title')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),

                    Forms\Components\RichEditor::make('description')
                        ->columnSpanFull(),

                    Forms\Components\KeyValue::make('extra_data')
                        ->label('Extra Data')
                        ->helperText('Additional key-value data (CTA text, subtitle, etc.)')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('video_path')
                        ->label('Video Profil Perusahaan')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->directory('homepage/videos')
                        ->maxSize(51200)
                        ->helperText('Opsional. Maks. 50MB. Muncul sebagai tombol "Lihat Video" di section ini (hero/about).')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Section Images')
                ->schema([
                    Forms\Components\Repeater::make('images')
                        ->relationship()
                        ->schema([
                            Forms\Components\FileUpload::make('image_path')
                                ->image()
                                ->directory('homepage')
                                ->required(),

                            Forms\Components\TextInput::make('caption')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section_key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('images_count')
                    ->counts('images')
                    ->label('Images'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageSections::route('/'),
            'create' => Pages\CreateHomepageSection::route('/create'),
            'edit' => Pages\EditHomepageSection::route('/{record}/edit'),
        ];
    }
}
