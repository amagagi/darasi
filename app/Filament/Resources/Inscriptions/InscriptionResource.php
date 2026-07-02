<?php

namespace App\Filament\Resources\Inscriptions;

use App\Models\Inscription;
use App\Models\Cours;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class InscriptionResource extends Resource
{
    protected static ?string $model = Inscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Inscriptions';

    protected static ?string $pluralModelLabel = 'Inscriptions';

    protected static ?string $modelLabel = 'Inscription';

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
                ->label('Cours')
                ->options(Cours::pluck('titre', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('progression')
                ->label('Progression (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(0)
                ->step(5)
                ->suffix('%'),

            Forms\Components\Toggle::make('tests_modules_valides')
                ->label('Tests modules validés')
                ->default(false),

            Forms\Components\DateTimePicker::make('date_debut')
                ->label('Date de début')
                ->default(now())
                ->required(),

            Forms\Components\DateTimePicker::make('date_completion')
                ->label('Date de complétion')
                ->nullable(),

            Forms\Components\Select::make('statut')
                ->label('Statut')
                ->options([
                    'actif' => 'Actif',
                    'suspendu' => 'Suspendu',
                    'termine' => 'Terminé',
                ])
                ->default('actif')
                ->required()
                ->native(false),

            Forms\Components\Select::make('abonnement_id')
                ->label('Abonnement lié')
                ->relationship('abonnement', 'id')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Optionnel : si l\'inscription vient d\'un abonnement'),

            Forms\Components\Toggle::make('est_via_abonnement')
                ->label('Via abonnement')
                ->default(false),
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
                    ->formatStateUsing(fn ($record) => $record->apprenant?->prenom . ' ' . $record->apprenant?->nom)
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('apprenant', function ($q) use ($search) {
                            $q->where('nom', 'like', "%{$search}%")
                              ->orWhere('prenom', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('cours.titre')
                    ->label('Cours')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('progression')
                    ->label('Progression')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => match(true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('tests_modules_valides')
                    ->label('Tests OK')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'success' => 'actif',
                        'warning' => 'suspendu',
                        'info' => 'termine',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_completion')
                    ->label('Completion')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('est_via_abonnement')
                    ->label('Abonnement')
                    ->boolean()
                    ->trueIcon('heroicon-o-credit-card')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'suspendu' => 'Suspendu',
                        'termine' => 'Terminé',
                    ]),

                Tables\Filters\SelectFilter::make('cours_id')
                    ->label('Cours')
                    ->relationship('cours', 'titre')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('est_via_abonnement')
                    ->label('Type')
                    ->options([
                        '1' => 'Via abonnement',
                        '0' => 'Achat direct',
                    ]),
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
            'index' => \App\Filament\Resources\Inscriptions\Pages\ListInscriptions::route('/'),
            'create' => \App\Filament\Resources\Inscriptions\Pages\CreateInscription::route('/create'),
            'edit' => \App\Filament\Resources\Inscriptions\Pages\EditInscription::route('/{record}/edit'),
        ];
    }
}