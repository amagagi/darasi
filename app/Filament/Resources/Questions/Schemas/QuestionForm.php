<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('test_id')
                    ->numeric(),
                TextInput::make('test_final_id')
                    ->numeric(),
                Textarea::make('question')
                    ->required()
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(['qcm' => 'Qcm', 'ouverte' => 'Ouverte'])
                    ->required(),
                TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('ordre')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
