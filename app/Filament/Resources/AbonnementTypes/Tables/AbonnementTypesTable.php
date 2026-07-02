<?php

namespace App\Filament\Resources\AbonnementTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbonnementTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('categorie_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nom')
                    ->searchable(),
                TextColumn::make('duree_jours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prix')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nb_cours_max')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('est_populaire')
                    ->boolean(),
                IconColumn::make('est_actif')
                    ->boolean(),
                TextColumn::make('ordre')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
