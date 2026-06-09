<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                TextInput::make('nom')
                    ->required()
                    ->maxLength(255),

                TextInput::make('prenom')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('telephone')
                    ->tel()
                    ->maxLength(20),

                DateTimePicker::make('email_verified_at')
                    ->label('Email vérifié le')
                    ->nullable()
                    ->visible(fn ($get) => $get('role') === 'formateur')
                    ->helperText('Laisser vide pour que le formateur attende la validation'),

                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'formateur' => 'Formateur',
                        'apprenant' => 'Apprenant',
                    ])
                    ->default('apprenant')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Si c'est un formateur et qu'on est en création, forcer non validé
                        if ($state === 'formateur' && !$get('id')) {
                            $set('email_verified_at', null);
                        }
                    }),

                TextInput::make('avatar')
                    ->maxLength(255),

                // Champ pour activer/désactiver le compte
                Toggle::make('is_active')
                    ->label('Compte actif')
                    ->default(true)
                    ->helperText('Décocher pour désactiver complètement le compte')
                    ->visible(fn ($record) => auth()->user()->role === 'admin'),

                // Raison de désactivation
                Textarea::make('deactivated_reason')
                    ->label('Raison de désactivation')
                    ->rows(2)
                    ->placeholder('Pourquoi ce compte est-il désactivé ?')
                    ->visible(fn ($get) => $get('is_active') === false)
                    ->nullable(),
            ]);
    }
}