<?php

namespace App\Filament\Resources\Modules\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cours_id')
                    ->required()
                    ->numeric(),
                TextInput::make('titre')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('ordre')
                    ->required()
                    ->numeric(),
                TextInput::make('duree_estimee')
                    ->numeric(),
            ]);
    }
}
