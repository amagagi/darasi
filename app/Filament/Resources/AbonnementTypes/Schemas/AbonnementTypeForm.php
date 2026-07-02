<?php

namespace App\Filament\Resources\AbonnementTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AbonnementTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('categorie_id')
                    ->numeric(),
                TextInput::make('nom')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('duree_jours')
                    ->required()
                    ->numeric(),
                TextInput::make('prix')
                    ->required()
                    ->numeric(),
                TextInput::make('nb_cours_max')
                    ->numeric(),
                Toggle::make('est_populaire')
                    ->required(),
                Toggle::make('est_actif')
                    ->required(),
                TextInput::make('ordre')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
