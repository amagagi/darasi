<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuestionInfolist
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
                TextEntry::make('question')
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('points')
                    ->numeric(),
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
