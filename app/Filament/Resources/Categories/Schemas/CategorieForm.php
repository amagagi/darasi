<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Pole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategorieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ✅ Sélecteur pour le pôle (au lieu de TextInput)
                Select::make('pole_id')
                    ->label('Pôle')
                    ->options(Pole::pluck('nom', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                // ✅ Nom avec génération automatique du slug
                TextInput::make('nom')
                    ->label('Nom de la catégorie')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => 
                        $set('slug', Str::slug($state))
                    ),

                // ✅ Slug généré automatiquement
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Généré automatiquement depuis le nom')
                    ->unique('categories', 'slug', ignoreRecord: true),

                // ✅ Description
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),

                // ✅ Ordre avec helper text
                TextInput::make('ordre')
                    ->label("Ordre d'affichage")
                    ->numeric()
                    ->default(0)
                    ->integer()
                    ->helperText('Plus le chiffre est petit, plus la catégorie apparaît en premier'),
            ]);
    }
}