<?php

namespace App\Filament\Resources\Tests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('module_id')
                    ->numeric(),
                TextEntry::make('titre'),
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
