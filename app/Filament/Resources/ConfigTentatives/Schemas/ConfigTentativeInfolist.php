<?php

namespace App\Filament\Resources\ConfigTentatives\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConfigTentativeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('test_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('test_final_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_tentatives')
                    ->numeric(),
                TextEntry::make('delai_heures')
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
