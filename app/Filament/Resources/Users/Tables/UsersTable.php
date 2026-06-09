<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Avatar
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->nom . ' ' . $record->prenom)
                    ),

                // Nom complet
                TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->getStateUsing(fn ($record) => $record->nom . ' ' . $record->prenom)
                    ->searchable(query: function ($query, $search) {
                        return $query->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('telephone')
                    ->toggleable(),

                // ROLE badge
                TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'formateur',
                        'success' => 'apprenant',
                    ])
                    ->sortable(),

                // Statut du compte (actif/désactivé)
                TextColumn::make('is_active')
                    ->label('Statut')
                    ->formatStateUsing(fn ($state) => $state ? '✅ Actif' : '❌ Désactivé')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                // Validation formateur
                TextColumn::make('email_verified_at')
                    ->label('Validation')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->role === 'formateur' 
                            ? ($state ? '✅ Validé' : '⏳ En attente')
                            : 'N/A'
                    )
                    ->badge()
                    ->color(fn ($state, $record) => 
                        $record->role !== 'formateur' 
                            ? 'gray' 
                            : ($state ? 'success' : 'warning')
                    ),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtre par rôle
                \Filament\Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'formateur' => 'Formateur',
                        'apprenant' => 'Apprenant',
                    ]),
                    
                // Filtre par statut
                \Filament\Tables\Filters\SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        '1' => 'Actifs',
                        '0' => 'Désactivés',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === '1') {
                            return $query->where('is_active', true);
                        }
                        if ($data['value'] === '0') {
                            return $query->where('is_active', false);
                        }
                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                
                // Action pour valider un formateur
                Action::make('validate_formateur')
                    ->label('Valider')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->visible(fn ($record) => $record->role === 'formateur' && !$record->email_verified_at)
                    ->requiresConfirmation()
                    ->modalHeading('Valider le formateur')
                    ->modalDescription('Ce formateur pourra se connecter après validation.')
                    ->action(function ($record) {
                        $record->validateByAdmin();
                        
                        Notification::make()
                            ->title('Formateur validé')
                            ->body('Le formateur peut maintenant se connecter.')
                            ->success()
                            ->send();
                    }),
                    
                // Action pour activer/désactiver un compte
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Désactiver' : 'Activer')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_active ? 'Désactiver le compte' : 'Activer le compte')
                    ->modalDescription(fn ($record) => $record->is_active 
                        ? 'Désactiver ce compte ? L\'utilisateur ne pourra plus se connecter.'
                        : 'Activer ce compte ? L\'utilisateur pourra se connecter.')
                    ->action(function ($record) {
                        if ($record->is_active) {
                            $record->deactivate('Désactivé par administrateur');
                            Notification::make()
                                ->title('Compte désactivé')
                                ->warning()
                                ->send();
                        } else {
                            $record->activate();
                            Notification::make()
                                ->title('Compte activé')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}