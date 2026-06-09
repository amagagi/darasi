<?php

namespace App\Filament\Resources\Cours\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CoursInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('titre'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('objectifs')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('prerequis')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('pole_id')
                    ->numeric(),
                TextEntry::make('formateur_id')
                    ->numeric(),
                TextEntry::make('categorie_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('niveau_id')
                    ->numeric()
                    ->placeholder('-'),
                ImageEntry::make('image_couverture')
                    ->placeholder('-'),
                TextEntry::make('video_presentation')
                    ->placeholder('-'),
                IconEntry::make('est_certifiant')
                    ->boolean(),
                TextEntry::make('note_minimale_certificat')
                    ->numeric(),
                TextEntry::make('prix')
                    ->numeric(),
                IconEntry::make('est_gratuit')
                    ->boolean(),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('note_moyenne')
                    ->numeric(),
                TextEntry::make('nb_apprenants')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
