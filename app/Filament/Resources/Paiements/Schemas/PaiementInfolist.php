<?php

namespace App\Filament\Resources\Paiements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaiementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('apprenant_id')
                    ->numeric(),
                TextEntry::make('cours_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('abonnement_type_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('montant')
                    ->numeric(),
                TextEntry::make('reference_komipay')
                    ->placeholder('-'),
                TextEntry::make('transaction_id')
                    ->placeholder('-'),
                TextEntry::make('tentatives')
                    ->numeric(),
                TextEntry::make('erreur_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('code_validation')
                    ->placeholder('-'),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('mode_paiement')
                    ->badge(),
                TextEntry::make('date_paiement')
                    ->dateTime()
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
