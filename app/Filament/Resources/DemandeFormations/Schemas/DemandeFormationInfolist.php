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
                // 🆕 Type de demande
                TextEntry::make('type')
                    ->label('Type de demande')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'formation' => '📚 Formation',
                        'assistance' => '🆘 Assistance',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'formation' => 'info',
                        'assistance' => 'warning',
                        default => 'gray',
                    })
                    ->columnSpanFull(),

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

                // 🔄 Label dynamique selon le type
                TextEntry::make('titre_cours_souhaite')
                    ->label(fn ($record) => $record->type === 'assistance'
                        ? '🆘 Sujet de l\'assistance'
                        : '📚 Cours souhaité')
                    ->badge()
                    ->color('primary'),

                // 🔄 Visible uniquement si formation
                TextEntry::make('domaine')
                    ->label('Domaine')
                    ->placeholder('Non précisé')
                    ->visible(fn ($record) => $record->type === 'formation'),

                // 🔄 Visible uniquement si formation
                TextEntry::make('niveau_souhaite')
                    ->label('Niveau souhaité')
                    ->placeholder('Non précisé')
                    ->visible(fn ($record) => $record->type === 'formation'),

                // 🔄 Label dynamique selon le type
                TextEntry::make('description')
                    ->label(fn ($record) => $record->type === 'assistance'
                        ? 'Description du problème'
                        : 'Description de la demande')
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
                    ->hidden(),
            ]);
    }
}