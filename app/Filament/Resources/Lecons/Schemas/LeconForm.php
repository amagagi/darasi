<?php

namespace App\Filament\Resources\Lecons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LeconForm
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
                Select::make('type_contenu')
                    ->options(['video' => 'Video', 'pdf' => 'Pdf', 'article' => 'Article'])
                    ->required(),
                Textarea::make('contenu_text')
                    ->columnSpanFull(),
                TextInput::make('url_video'),
                TextInput::make('url_pdf'),
                TextInput::make('duree_video')
                    ->numeric(),
                TextInput::make('ordre')
                    ->required()
                    ->numeric(),
            ]);
    }
}
