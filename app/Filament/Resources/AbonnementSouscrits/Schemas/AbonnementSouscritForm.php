<?php

namespace App\Filament\Resources\AbonnementSouscrits\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AbonnementSouscritForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('apprenant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type_abonnement_id')
                    ->required()
                    ->numeric(),
                TextInput::make('categorie_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('date_debut')
                    ->required(),
                DateTimePicker::make('date_fin')
                    ->required(),
                Select::make('statut')
                    ->options([
            'actif' => 'Actif',
            'expire' => 'Expire',
            'annule' => 'Annule',
            'suspendu' => 'Suspendu',
            'en_attente' => 'En attente',
        ])
                    ->default('actif')
                    ->required(),
                TextInput::make('paiement_id')
                    ->numeric(),
            ]);
    }
}
