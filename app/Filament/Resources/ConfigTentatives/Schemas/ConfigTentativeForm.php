<?php

namespace App\Filament\Resources\ConfigTentatives\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConfigTentativeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('test_id')
                    ->numeric(),
                TextInput::make('test_final_id')
                    ->numeric(),
                TextInput::make('max_tentatives')
                    ->required()
                    ->numeric()
                    ->default(3),
                TextInput::make('delai_heures')
                    ->required()
                    ->numeric()
                    ->default(24),
            ]);
    }
}
