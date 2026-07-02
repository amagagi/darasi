<?php

namespace App\Filament\Resources\Paiements;

use App\Models\Paiement;
use App\Models\Cours;
use App\Models\User;
use App\Models\AbonnementType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class PaiementResource extends Resource
{
    protected static ?string $model = Paiement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Paiements';

    protected static ?string $pluralModelLabel = 'Paiements';

    protected static ?string $modelLabel = 'Paiement';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('apprenant_id')
                ->label('Apprenant')
                ->options(User::where('role', 'apprenant')->get()->mapWithKeys(fn ($user) => [
                    $user->id => $user->prenom . ' ' . $user->nom . ' (' . $user->email . ')'
                ]))
                ->searchable()
                ->preload()
                ->required()
                ->native(false),

            Forms\Components\Select::make('cours_id')
                ->label('Cours (optionnel)')
                ->options(Cours::pluck('titre', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
                ->native(false),

            Forms\Components\Select::make('abonnement_type_id')
                ->label('Abonnement (optionnel)')
                ->options(AbonnementType::where('est_actif', true)->pluck('nom', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
                ->native(false),

            Forms\Components\TextInput::make('montant')
                ->label('Montant (FCFA)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->prefix('FCFA'),

            Forms\Components\TextInput::make('reference_komipay')
                ->label('Référence Komipay')
                ->maxLength(100)
                ->nullable(),

            Forms\Components\TextInput::make('transaction_id')
                ->label('ID Transaction')
                ->maxLength(191)
                ->nullable()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('statut')
                ->label('Statut')
                ->options([
                    'en_attente' => '⏳ En attente',
                    'paye' => '✅ Payé',
                    'echoue' => '❌ Échoué',
                    'rembourse' => '🔄 Remboursé',
                ])
                ->default('en_attente')
                ->required()
                ->native(false),

            Forms\Components\Select::make('mode_paiement')
                ->label('Mode de paiement')
                ->options([
                    'AMANATA' => 'Amanata',
                    'MY_NITA' => 'My Nita',
                    'CARTE' => 'Carte bancaire',
                    'AIRTEL_MONEY' => 'Airtel Money',
                    'CREDIT_CARD' => 'Carte de crédit',
                ])
                ->required()
                ->native(false),

            Forms\Components\DateTimePicker::make('date_paiement')
                ->label('Date de paiement')
                ->nullable(),

            Forms\Components\TextInput::make('tentatives')
                ->label('Tentatives')
                ->numeric()
                ->default(0)
                ->integer()
                ->minValue(0),

            Forms\Components\Textarea::make('erreur_message')
                ->label('Message d\'erreur')
                ->rows(2)
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('code_validation')
                ->label('Code de validation')
                ->maxLength(50)
                ->nullable(),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('apprenant.prenom')
                    ->label('Apprenant')
                    ->formatStateUsing(fn ($record) => $record->apprenant?->prenom . ' ' . $record->apprenant?->nom)
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('apprenant', function ($q) use ($search) {
                            $q->where('nom', 'like', "%{$search}%")
                              ->orWhere('prenom', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->money('XOF', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('cours.titre')
                    ->label('Cours')
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('abonnementType.nom')
                    ->label('Abonnement')
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'warning' => 'en_attente',
                        'success' => 'paye',
                        'danger' => 'echoue',
                        'info' => 'rembourse',
                    ])
                    ->icons([
                        'en_attente' => 'heroicon-o-clock',
                        'paye' => 'heroicon-o-check-circle',
                        'echoue' => 'heroicon-o-x-circle',
                        'rembourse' => 'heroicon-o-arrow-path',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('mode_paiement')
                    ->label('Mode')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'AMANATA' => 'primary',
                        'MY_NITA' => 'success',
                        'CARTE' => 'warning',
                        'AIRTEL_MONEY' => 'info',
                        'CREDIT_CARD' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_komipay')
                    ->label('Référence')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('tentatives')
                    ->label('Tent.')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('date_paiement')
                    ->label('Date paiement')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

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
                        'en_attente' => 'En attente',
                        'paye' => 'Payé',
                        'echoue' => 'Échoué',
                        'rembourse' => 'Remboursé',
                    ]),

                Tables\Filters\SelectFilter::make('mode_paiement')
                    ->label('Mode de paiement')
                    ->options([
                        'AMANATA' => 'Amanata',
                        'MY_NITA' => 'My Nita',
                        'CARTE' => 'Carte bancaire',
                        'AIRTEL_MONEY' => 'Airtel Money',
                        'CREDIT_CARD' => 'Carte de crédit',
                    ]),

                Tables\Filters\Filter::make('date_paiement')
                    ->label('Date de paiement')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date_paiement', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date_paiement', '<=', $data['until']));
                    }),
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
            'index' => \App\Filament\Resources\Paiements\Pages\ListPaiements::route('/'),
            'create' => \App\Filament\Resources\Paiements\Pages\CreatePaiement::route('/create'),
            'edit' => \App\Filament\Resources\Paiements\Pages\EditPaiement::route('/{record}/edit'),
        ];
    }
}