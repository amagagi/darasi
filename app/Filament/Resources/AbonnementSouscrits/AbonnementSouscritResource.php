<?php

namespace App\Filament\Resources\AbonnementSouscrits;

use App\Models\AbonnementSouscrit;
use App\Models\User;
use App\Models\AbonnementType;
use App\Models\Categorie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class AbonnementSouscritResource extends Resource
{
    protected static ?string $model = AbonnementSouscrit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Abonnements actifs';

    protected static ?string $pluralModelLabel = 'Abonnements souscrits';

    protected static ?string $modelLabel = 'Abonnement';

    // =========================
    // FORM (lecture et modification limitée)
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('apprenant_id')
                ->label('Apprenant')
                ->options(User::where('role', 'apprenant')->get()->mapWithKeys(fn ($user) => [
                    $user->id => $user->prenom . ' ' . $user->nom
                ]))
                ->searchable()
                ->preload()
                ->required()
                ->disabled()
                ->dehydrated(false),

            Forms\Components\Select::make('type_abonnement_id')
                ->label('Type d\'abonnement')
                ->options(AbonnementType::pluck('nom', 'id'))
                ->searchable()
                ->disabled()
                ->dehydrated(false),

            Forms\Components\Select::make('categorie_id')
                ->label('Catégorie')
                ->options(Categorie::pluck('nom', 'id'))
                ->searchable()
                ->nullable()
                ->disabled()
                ->dehydrated(false),

            Forms\Components\DateTimePicker::make('date_debut')
                ->label('Date de début')
                ->required()
                ->disabled(),

            Forms\Components\DateTimePicker::make('date_fin')
                ->label('Date de fin')
                ->required()
                ->disabled(),

            Forms\Components\Select::make('statut')
                ->label('Statut')
                ->options([
                    'actif' => '🟢 Actif',
                    'expire' => '🔴 Expiré',
                    'annule' => '⛔ Annulé',
                    'suspendu' => '🟡 Suspendu',
                    'en_attente' => '⏳ En attente',
                ])
                ->required()
                ->native(false),

            Forms\Components\Select::make('paiement_id')
                ->label('Paiement associé')
                ->relationship('paiement', 'id')
                ->searchable()
                ->nullable()
                ->disabled()
                ->dehydrated(false),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('apprenant.prenom')
                    ->label('Apprenant')
                    ->formatStateUsing(fn ($record) => 
                        $record->apprenant?->prenom . ' ' . $record->apprenant?->nom
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('typeAbonnement.nom')
                    ->label('Forfait')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('categorie.nom')
                    ->label('Catégorie')
                    ->formatStateUsing(fn ($state) => $state ?? '🌍 Général')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state < now() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'success' => 'actif',
                        'danger' => 'expire',
                        'danger' => 'annule',
                        'warning' => 'suspendu',
                        'info' => 'en_attente',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('paiement.montant')
                    ->label('Montant payé')
                    ->money('XOF', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actifs',
                        'expire' => 'Expirés',
                        'annule' => 'Annulés',
                        'suspendu' => 'Suspendus',
                    ]),

                Tables\Filters\Filter::make('date_fin')
                    ->label('Expiration')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Expire après le'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date_fin', '<=', $data['from']));
                    }),

                Tables\Filters\SelectFilter::make('type_abonnement_id')
                    ->label('Forfait')
                    ->relationship('typeAbonnement', 'nom'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->role === 'admin'),
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->role === 'admin'),
            ]);
    }

    // =========================
    // PAGES
    // =========================
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AbonnementSouscrits\Pages\ListAbonnementSouscrits::route('/'),
            'edit' => \App\Filament\Resources\AbonnementSouscrits\Pages\EditAbonnementSouscrit::route('/{record}/edit'),
        ];
    }
}