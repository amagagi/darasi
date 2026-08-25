<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\Pages\ListPartners;
use App\Models\Partner;
use App\Services\ImageOptimizerService;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Partenaires';

    protected static ?string $pluralModelLabel = 'Partenaires';

    protected static ?string $modelLabel = 'Partenaire';

    protected static ?string $slug = 'partners';

    protected static ?string $recordTitleAttribute = 'name';

    /** Compteur des partenaires actuellement affichés sur le site. */
    public static function getNavigationBadge(): ?string
    {
        try {
            $actifs = Partner::query()->active()->count();
        } catch (\Throwable) {
            return null;
        }

        return $actifs > 0 ? (string) $actifs : null;
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
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->directory('partners')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(2048) // Ko — 2 Mo
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                        ->helperText('Formats acceptés : JPG, PNG, WEBP, SVG. Taille maximale : 2 Mo.')
                        ->afterStateUpdated(function (?string $state): void {
                            if ($state === null) {
                                return;
                            }

                            $optimizer = app(ImageOptimizerService::class);
                            $optimizer->optimize($state);
                            $optimizer->thumbnail($state);
                        })
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('website_url')
                        ->label('Site web (optionnel)')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://exemple.com'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description (optionnelle)')
                        ->rows(2)
                        ->helperText('Utilisée en info-bulle / texte alternatif du logo.')
                        ->columnSpanFull(),
                ]),

            Section::make('Affichage')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('display_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Les partenaires sont aussi réordonnables par glisser-déposer dans la liste.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Visible sur le site')
                        ->default(true),
                ]),
        ]);
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

                Tables\Columns\TextColumn::make('website_url')
                    ->label('Site web')
                    ->url(fn (Partner $record) => $record->website_url, shouldOpenInNewTab: true)
                    ->limit(40)
                    ->placeholder('—')
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visible')
                    ->trueLabel('Visibles')
                    ->falseLabel('Masqués')
                    ->placeholder('Tous'),
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
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }
}
