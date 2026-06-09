<?php

namespace App\Filament\Resources\DemandeFormations;

use App\Models\DemandesFormation;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class DemandeFormationResource extends Resource
{
    protected static ?string $model = DemandesFormation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Demandes formation';

    protected static ?string $pluralModelLabel = 'Demandes de formation';

    protected static ?string $modelLabel = 'Demande';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\TextInput::make('nom')
                ->label('Nom')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(150),

            Forms\Components\TextInput::make('telephone')
                ->label('Téléphone')
                ->maxLength(20),

            Forms\Components\TextInput::make('titre_cours_souhaite')
                ->label('Titre du cours souhaité')
                ->required()
                ->maxLength(200),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('domaine')
                ->label('Domaine')
                ->maxLength(100),

            Forms\Components\TextInput::make('niveau_souhaite')
                ->label('Niveau souhaité')
                ->maxLength(100),

            Forms\Components\Select::make('statut')
                ->label('Statut')
                ->options([
                    'en_attente' => '⏳ En attente',
                    'prise_en_compte' => '📋 Prise en compte',
                    'realise' => '✅ Réalisé',
                    'rejete' => '❌ Rejeté',
                ])
                ->default('en_attente')
                ->required()
                ->reactive()
                ->native(false),

            Forms\Components\DateTimePicker::make('traite_le')
                ->label('Traité le')
                ->nullable()
                ->visible(fn ($get) => $get('statut') !== 'en_attente'),

            Forms\Components\Select::make('traite_par')
                ->label('Traité par')
                ->options(User::pluck('nom', 'id'))
                ->searchable()
                ->nullable()
                ->default(auth()->id())
                ->visible(fn ($get) => $get('statut') !== 'en_attente'),

            Forms\Components\Textarea::make('commentaire_admin')
                ->label('Commentaire admin')
                ->rows(3)
                ->placeholder('Réponse à la demande...')
                ->visible(fn ($get) => $get('statut') !== 'en_attente'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nom')
                    ->label('Demandeur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('titre_cours_souhaite')
                    ->label('Cours souhaité')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('domaine')
                    ->label('Domaine')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'warning' => 'en_attente',
                        'info' => 'prise_en_compte',
                        'success' => 'realise',
                        'danger' => 'rejete',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date demande')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('traitePar.nom')
                    ->label('Traité par')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'prise_en_compte' => 'Prise en compte',
                        'realise' => 'Réalisé',
                        'rejete' => 'Rejeté',
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
            'index' => \App\Filament\Resources\DemandeFormations\Pages\ListDemandeFormations::route('/'),
            'create' => \App\Filament\Resources\DemandeFormations\Pages\CreateDemandeFormation::route('/create'),
            'edit' => \App\Filament\Resources\DemandeFormations\Pages\EditDemandeFormation::route('/{record}/edit'),
        ];
    }
}