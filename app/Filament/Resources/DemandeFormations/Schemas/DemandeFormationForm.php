<?php

namespace App\Filament\Resources\DemandeFormations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use App\Models\User;

class DemandeFormationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->required()
                    ->maxLength(100),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(150),

                TextInput::make('telephone')
                    ->label('Téléphone')
                    ->maxLength(20),

                TextInput::make('titre_cours_souhaite')
                    ->label('Titre du cours souhaité')
                    ->required()
                    ->maxLength(200),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('domaine')
                    ->label('Domaine')
                    ->maxLength(100),

                TextInput::make('niveau_souhaite')
                    ->label('Niveau souhaité')
                    ->maxLength(100),

                Select::make('statut')
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

                DateTimePicker::make('traite_le')
                    ->label('Traité le')
                    ->nullable()
                    ->visible(fn ($get) => $get('statut') !== 'en_attente'),

                Select::make('traite_par')
                    ->label('Traité par')
                    ->options(User::pluck('nom', 'id'))
                    ->searchable()
                    ->nullable()
                    ->default(auth()->id())
                    ->visible(fn ($get) => $get('statut') !== 'en_attente'),

                Textarea::make('commentaire_admin')
                    ->label('Commentaire admin')
                    ->rows(3)
                    ->placeholder('Réponse à la demande...')
                    ->visible(fn ($get) => $get('statut') !== 'en_attente'),
            ]);
    }
}