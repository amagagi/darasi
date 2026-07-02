<?php

namespace App\Filament\Resources\Lecons\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeconInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('module_id')
                    ->numeric(),
                TextEntry::make('titre'),
                TextEntry::make('type_contenu')
                    ->badge(),
                TextEntry::make('contenu_text')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('url_video')
                    ->placeholder('-'),
                TextEntry::make('url_pdf')
                    ->placeholder('-'),
                TextEntry::make('duree_video')
                    ->numeric()
                    ->placeholder('-'),
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
