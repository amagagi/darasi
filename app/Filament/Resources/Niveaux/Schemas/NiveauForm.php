<?php

namespace App\Filament\Resources\Niveaux\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NiveauForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pole_id')
                    ->required()
                    ->numeric(),
                TextInput::make('libelle')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('ordre')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
