<?php

namespace App\Filament\Resources\ContenuJuridiques\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContenuJuridiqueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type'),
                TextEntry::make('titre'),
                TextEntry::make('contenu')
                    ->columnSpanFull(),
                IconEntry::make('est_actif')
                    ->boolean()
                    ->placeholder('-'),
                TextEntry::make('modifie_par')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('date_modification')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
