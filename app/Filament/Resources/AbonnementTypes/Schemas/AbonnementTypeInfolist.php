<?php

namespace App\Filament\Resources\AbonnementTypes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AbonnementTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('categorie_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('nom'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('duree_jours')
                    ->numeric(),
                TextEntry::make('prix')
                    ->numeric(),
                TextEntry::make('nb_cours_max')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('est_populaire')
                    ->boolean(),
                IconEntry::make('est_actif')
                    ->boolean(),
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
