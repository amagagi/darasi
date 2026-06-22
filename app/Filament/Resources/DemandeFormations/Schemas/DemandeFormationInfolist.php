<?php

namespace App\Filament\Resources\DemandeFormations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Schema;

class DemandeFormationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nom')
                    ->label('Nom complet')
                    ->weight('bold'),

                TextEntry::make('email')
                    ->label('Email')
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextEntry::make('telephone')
                    ->label('Téléphone')
                    ->placeholder('Non renseigné')
                    ->icon('heroicon-o-phone'),

                TextEntry::make('titre_cours_souhaite')
                    ->label('Cours souhaité')
                    ->badge()
                    ->color('primary'),

                TextEntry::make('domaine')
                    ->label('Domaine')
                    ->placeholder('Non précisé'),

                TextEntry::make('niveau_souhaite')
                    ->label('Niveau souhaité')
                    ->placeholder('Non précisé'),

                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('Aucune description')
                    ->columnSpanFull(),

                TextEntry::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'en_attente' => 'warning',
                        'prise_en_compte' => 'info',
                        'realise' => 'success',
                        'rejete' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'en_attente' => '⏳ En attente',
                        'prise_en_compte' => '📋 Prise en compte',
                        'realise' => '✅ Réalisé',
                        'rejete' => '❌ Rejeté',
                        default => $state,
                    }),

                TextEntry::make('traitePar.nom')
                    ->label('Traité par')
                    ->placeholder('Non traité'),

                TextEntry::make('traite_le')
                    ->label('Traité le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non traité'),

                TextEntry::make('commentaire_admin')
                    ->label('Commentaire admin')
                    ->placeholder('Aucun commentaire')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('Date de la demande')
                    ->dateTime('d/m/Y H:i')
                    ->icon('heroicon-o-calendar'),

                TextEntry::make('updated_at')
                    ->label('Dernière modification')
                    ->dateTime('d/m/Y H:i')
                    ->hidden(),  // ← CORRIGÉ
            ]);
    }
}