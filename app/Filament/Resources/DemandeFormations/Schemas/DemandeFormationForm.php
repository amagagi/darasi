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
                // 🆕 Type de demande
                Select::make('type')
                    ->label('Type de demande')
                    ->options([
                        'formation' => '📚 Demande de formation',
                        'assistance' => '🆘 Demande d\'assistance',
                    ])
                    ->default('formation')
                    ->required()
                    ->reactive()
                    ->native(false)
                    ->columnSpanFull(),

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

                // 🔄 Label dynamique
                TextInput::make('titre_cours_souhaite')
                    ->label(fn ($get) => $get('type') === 'assistance'
                        ? '🆘 Sujet de l\'assistance'
                        : '📚 Titre du cours souhaité')
                    ->required()
                    ->maxLength(200),

                // 🔄 Label dynamique
                Textarea::make('description')
                    ->label(fn ($get) => $get('type') === 'assistance'
                        ? 'Description du problème'
                        : 'Description de la demande')
                    ->rows(4)
                    ->columnSpanFull(),

                // 🔄 Visible uniquement pour formation
                TextInput::make('domaine')
                    ->label('Domaine')
                    ->maxLength(100)
                    ->visible(fn ($get) => $get('type') === 'formation'),

                // 🔄 Visible uniquement pour formation
                TextInput::make('niveau_souhaite')
                    ->label('Niveau souhaité')
                    ->maxLength(100)
                    ->visible(fn ($get) => $get('type') === 'formation'),

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