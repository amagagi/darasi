<?php

namespace App\Filament\Resources\Inscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('apprenant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('cours_id')
                    ->required()
                    ->numeric(),
                TextInput::make('progression')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('tests_modules_valides')
                    ->required(),
                DateTimePicker::make('date_debut')
                    ->required(),
                DateTimePicker::make('date_completion'),
                Select::make('statut')
                    ->options(['actif' => 'Actif', 'suspendu' => 'Suspendu', 'termine' => 'Termine'])
                    ->default('actif')
                    ->required(),
                TextInput::make('abonnement_id')
                    ->numeric(),
                Toggle::make('est_via_abonnement')
                    ->required(),
            ]);
    }
}
