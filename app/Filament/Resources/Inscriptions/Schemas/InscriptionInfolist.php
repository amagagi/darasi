<?php

namespace App\Filament\Resources\Inscriptions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('apprenant_id')
                    ->numeric(),
                TextEntry::make('cours_id')
                    ->numeric(),
                TextEntry::make('progression')
                    ->numeric(),
                IconEntry::make('tests_modules_valides')
                    ->boolean(),
                TextEntry::make('date_debut')
                    ->dateTime(),
                TextEntry::make('date_completion')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('abonnement_id')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('est_via_abonnement')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
