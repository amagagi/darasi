<?php

namespace App\Filament\Resources\ContenuJuridiques\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContenuJuridiqueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type')
                    ->required(),
                TextInput::make('titre')
                    ->required(),
                Textarea::make('contenu')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('est_actif'),
                TextInput::make('modifie_par')
                    ->numeric(),
                DateTimePicker::make('date_modification'),
            ]);
    }
}
