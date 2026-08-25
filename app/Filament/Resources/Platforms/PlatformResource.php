<?php

namespace App\Filament\Resources\Platforms;

use App\Filament\Resources\Platforms\Pages\CreatePlatform;
use App\Filament\Resources\Platforms\Pages\EditPlatform;
use App\Filament\Resources\Platforms\Pages\ListPlatforms;
use App\Models\Platform;
use App\Services\ImageOptimizerService;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class PlatformResource extends Resource
{
    protected static ?string $model = Platform::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Plateformes';

    protected static ?string $pluralModelLabel = 'Plateformes';

    protected static ?string $modelLabel = 'Plateforme';

    protected static ?string $slug = 'platforms';

    protected static ?string $recordTitleAttribute = 'name';

    private const CATEGORIES = [
        'e-learning' => 'E-learning',
        'gestion' => 'Gestion',
        'mobile' => 'Application mobile',
        'autre' => 'Autre',
    ];

    /** Compteur des plateformes actuellement visibles sur le site. */
    public static function getNavigationBadge(): ?string
    {
        try {
            $actives = Platform::query()->active()->count();
        } catch (\Throwable) {
            return null;
        }

        return $actives > 0 ? (string) $actives : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Contenu')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('short_description')
                        ->label('Description courte')
                        ->required()
                        ->maxLength(160)
                        ->helperText('~160 caractères, utilisée sur la carte de la page liste.')
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('description')
                        ->label('Description complète (page détail)')
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'link',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'blockquote',
                        ]),

                    Forms\Components\TextInput::make('url')
                        ->label('Lien vers la plateforme')
                        ->required()
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://...'),

                    Forms\Components\Select::make('category')
                        ->label('Catégorie (optionnelle)')
                        ->options(self::CATEGORIES)
                        ->native(false),
                ]),

            Section::make('Visuels')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo (optionnel)')
                        ->image()
                        ->directory('platforms/logos')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(2048) // Ko — 2 Mo
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                        ->helperText('Formats acceptés : JPG, PNG, WEBP, SVG. Taille maximale : 2 Mo.')
                        ->afterStateUpdated(fn (?string $state) => self::optimiser($state)),

                    Forms\Components\FileUpload::make('cover_image_path')
                        ->label('Image de couverture (optionnelle)')
                        ->image()
                        ->directory('platforms/covers')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(5120) // Ko — 5 Mo
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Formats acceptés : JPG, PNG, WEBP. Taille maximale : 5 Mo.')
                        ->afterStateUpdated(fn (?string $state) => self::optimiser($state)),
                ]),

            Section::make('Affichage')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('display_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Les plateformes sont aussi réordonnables par glisser-déposer dans la liste.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Visible sur le site')
                        ->default(true),
                ]),
        ]);
    }

    private static function optimiser(?string $state): void
    {
        if ($state === null) {
            return;
        }

        $optimizer = app(ImageOptimizerService::class);
        $optimizer->optimize($state);
        $optimizer->thumbnail($state);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->formatStateUsing(fn (?string $state) => $state ? (self::CATEGORIES[$state] ?? $state) : '—')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('Lien')
                    ->url(fn (Platform $record) => $record->url, shouldOpenInNewTab: true)
                    ->limit(40)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(self::CATEGORIES),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visible')
                    ->trueLabel('Visibles')
                    ->falseLabel('Masquées')
                    ->placeholder('Toutes'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatforms::route('/'),
            'create' => CreatePlatform::route('/create'),
            'edit' => EditPlatform::route('/{record}/edit'),
        ];
    }
}
