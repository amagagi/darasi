<?php

namespace App\Filament\Resources\AbonnementSouscrits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AbonnementSouscritInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('apprenant_id')
                    ->numeric(),
                TextEntry::make('type_abonnement_id')
                    ->numeric(),
                TextEntry::make('categorie_id')
                    ->numeric(),
                TextEntry::make('date_debut')
                    ->dateTime(),
                TextEntry::make('date_fin')
                    ->dateTime(),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('paiement_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
