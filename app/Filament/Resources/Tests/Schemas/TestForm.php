<?php

namespace App\Filament\Resources\Tests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('module_id')
                    ->required()
                    ->numeric(),
                TextInput::make('titre')
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
