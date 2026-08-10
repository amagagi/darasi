<?php

namespace App\Filament\Resources\ContenusSite;

use App\Filament\Resources\ContenusSite\Pages\CreateContenuSite;
use App\Filament\Resources\ContenusSite\Pages\EditContenuSite;
use App\Filament\Resources\ContenusSite\Pages\ListContenusSite;
use App\Models\ContenuSite;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
// En Filament v5 les composants de mise en page vivent dans Schemas, pas Forms.
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ContenuSiteResource extends Resource
{
    protected static ?string $model = ContenuSite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Vision & Mission';

    protected static ?string $pluralModelLabel = 'Blocs de contenu';

    protected static ?string $modelLabel = 'Bloc de contenu';

    protected static ?string $slug = 'contenus-site';

    protected static ?string $recordTitleAttribute = 'titre';

    /**
     * Icônes proposées à l'administrateur. Les valeurs correspondent aux noms
     * résolus côté Flutter dans `vision_mission_section.dart` : y ajouter une
     * entrée impose d'étendre la correspondance côté frontend.
     */
    public const ICONES = [
        'visibility' => '👁️ Œil (vision)',
        'flag' => '🚩 Drapeau (mission)',
        'favorite' => '❤️ Cœur (valeurs)',
        'rocket_launch' => '🚀 Fusée (ambition)',
        'handshake' => '🤝 Poignée de main (partenariat)',
        'school' => '🎓 Diplôme (formation)',
        'lightbulb' => '💡 Ampoule (innovation)',
        'public' => '🌍 Globe (portée)',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Identification')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('cle')
                        ->label('Clé technique')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->helperText('Identifiant stable utilisé par le site (vision, mission, valeurs). À ne plus modifier une fois créé.'),

                    Forms\Components\Select::make('icone')
                        ->label('Icône')
                        ->options(self::ICONES)
                        ->native(false)
                        ->helperText('Affichée au-dessus du titre sur la page d\'accueil.'),
                ]),

            Section::make('Contenu affiché')
                ->schema([
                    Forms\Components\TextInput::make('titre')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ex : Notre Vision'),

                    Forms\Components\TextInput::make('sous_titre')
                        ->label('Sous-titre')
                        ->maxLength(255)
                        ->placeholder('Ex : Rendre l\'excellence accessible à tous'),

                    Forms\Components\Textarea::make('contenu')
                        ->label('Texte')
                        ->required()
                        ->rows(8)
                        ->columnSpanFull()
                        ->helperText('Texte simple. Laissez une ligne vide entre deux paragraphes pour les séparer à l\'affichage.'),
                ]),

            Section::make('Affichage')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('ordre')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Croissant : 1 s\'affiche avant 2.'),

                    Forms\Components\Toggle::make('est_actif')
                        ->label('Actif')
                        ->default(true)
                        ->helperText('Décoché, le bloc disparaît du site sans être supprimé.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ordre')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cle')
                    ->label('Clé')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sous_titre')
                    ->label('Sous-titre')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('modifiePar.nom')
                    ->label('Modifié par')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ordre')
            ->filters([
                Tables\Filters\TernaryFilter::make('est_actif')
                    ->label('Actif')
                    ->trueLabel('Actifs')
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
            'index' => ListContenusSite::route('/'),
            'create' => CreateContenuSite::route('/create'),
            'edit' => EditContenuSite::route('/{record}/edit'),
        ];
    }
}
