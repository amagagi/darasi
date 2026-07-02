<?php

namespace App\Filament\Resources\Certificats\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CertificatInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('inscription_id')
                    ->numeric(),
                TextEntry::make('tentative_final_id')
                    ->numeric(),
                TextEntry::make('code_verification'),
                TextEntry::make('url_pdf')
                    ->placeholder('-'),
                TextEntry::make('date_emission')
                    ->dateTime(),
                IconEntry::make('est_valide')
                    ->boolean(),
                TextEntry::make('date_revocation')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('revoque_par')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('motif_revocation')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
