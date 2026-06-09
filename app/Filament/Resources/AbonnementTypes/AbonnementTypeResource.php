<?php

namespace App\Filament\Resources\AbonnementTypes;

use App\Models\AbonnementType;
use App\Models\Categorie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class AbonnementTypeResource extends Resource
{
    protected static ?string $model = AbonnementType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Forfaits';

    protected static ?string $pluralModelLabel = 'Types d\'abonnement';

    protected static ?string $modelLabel = 'Abonnement';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('categorie_id')
                ->label('Catégorie (optionnelle)')
                ->options(Categorie::pluck('nom', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Laisser vide pour un abonnement général (toutes catégories)')
                ->native(false),

            Forms\Components\TextInput::make('nom')
                ->label('Nom de l\'abonnement')
                ->required()
                ->maxLength(100),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('duree_jours')
                ->label('Durée (en jours)')
                ->numeric()
                ->required()
                ->minValue(1)
                ->integer()
                ->suffix(' jours'),

            Forms\Components\TextInput::make('prix')
                ->label('Prix (FCFA)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->integer()
                ->prefix('FCFA'),

            Forms\Components\TextInput::make('nb_cours_max')
                ->label('Nombre de cours max')
                ->numeric()
                ->nullable()
                ->integer()
                ->minValue(0)
                ->helperText('Laisser vide pour illimité'),

            Forms\Components\Toggle::make('est_populaire')
                ->label('Populaire')
                ->default(false)
                ->helperText('Mettre en avant cet abonnement'),

            Forms\Components\Toggle::make('est_actif')
                ->label('Actif')
                ->default(true),

            Forms\Components\TextInput::make('ordre')
                ->label("Ordre d'affichage")
                ->numeric()
                ->default(0)
                ->integer()
                ->helperText('Plus le chiffre est petit, plus l\'abonnement apparaît en premier'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('nom')
                    ->label('Forfait')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('categorie.nom')
                    ->label('Catégorie')
                    ->formatStateUsing(fn ($state) => $state ?? '🌍 Général')
                    ->badge()
                    ->color(fn ($state) => $state ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('duree_jours')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state . ' jours')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('prix')
                    ->label('Prix')
                    ->money('XOF', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nb_cours_max')
                    ->label('Cours max')
                    ->formatStateUsing(fn ($state) => $state ?: '∞')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('est_populaire')
                    ->label('Populaire')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning'),

                Tables\Columns\IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('souscriptions_count')
                    ->label('Souscriptions')
                    ->counts('souscriptions')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ordre')
            ->filters([
                Tables\Filters\SelectFilter::make('est_actif')
                    ->label('Statut')
                    ->options([
                        '1' => 'Actifs',
                        '0' => 'Inactifs',
                    ]),
                Tables\Filters\SelectFilter::make('est_populaire')
                    ->label('Populaires')
                    ->options([
                        '1' => 'Populaires',
                        '0' => 'Non populaires',
                    ]),
                Tables\Filters\SelectFilter::make('categorie_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    // =========================
    // PAGES
    // =========================
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AbonnementTypes\Pages\ListAbonnementTypes::route('/'),
            'create' => \App\Filament\Resources\AbonnementTypes\Pages\CreateAbonnementType::route('/create'),
            'edit' => \App\Filament\Resources\AbonnementTypes\Pages\EditAbonnementType::route('/{record}/edit'),
        ];
    }
}