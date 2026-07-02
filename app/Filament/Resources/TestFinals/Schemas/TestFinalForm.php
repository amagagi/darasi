<?php

namespace App\Filament\Resources\TestFinals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Cours;

class TestFinalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cours_id')
                    ->label('Cours')
                    ->options(Cours::pluck('titre', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Un seul test final par cours')
                    ->native(false),

                TextInput::make('titre')
                    ->label('Titre du test final')
                    ->required()
                    ->maxLength(200),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('note_minimale')
                    ->label('Note minimale pour réussite (%)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(70)
                    ->suffix('%'),

                TextInput::make('duree_limite')
                    ->label('Durée limite (minutes)')
                    ->numeric()
                    ->nullable()
                    ->integer()
                    ->minValue(1)
                    ->suffix('min')
                    ->helperText('Laisser vide pour illimité'),
            ]);
    }
}