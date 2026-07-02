<?php

namespace App\Filament\Resources\Niveaux\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NiveauInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('pole_id')
                    ->numeric(),
                TextEntry::make('libelle'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('ordre')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
