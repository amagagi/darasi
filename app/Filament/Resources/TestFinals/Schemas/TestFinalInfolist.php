<?php

namespace App\Filament\Resources\TestFinals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TestFinalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('cours.titre')
                    ->label('Cours')
                    ->badge()
                    ->color('primary'),

                TextEntry::make('titre')
                    ->label('Titre du test final')
                    ->weight('bold'),

                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('Aucune description')
                    ->columnSpanFull(),

                TextEntry::make('note_minimale')
                    ->label('Note minimale')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('warning'),

                TextEntry::make('duree_limite')
                    ->label('Durée limite')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' minutes' : 'Illimité'),

                TextEntry::make('questions_count')
                    ->label('Nombre de questions')
                    ->state(fn ($record) => $record->questions()->count())
                    ->badge()
                    ->color('success'),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),

                TextEntry::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}