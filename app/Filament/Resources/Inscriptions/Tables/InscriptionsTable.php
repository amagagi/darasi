<?php

namespace App\Filament\Resources\Inscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('apprenant_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cours_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('progression')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('tests_modules_valides')
                    ->boolean(),
                TextColumn::make('date_debut')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_completion')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('statut')
                    ->badge(),
                TextColumn::make('abonnement_id')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('est_via_abonnement')
                    ->boolean(),
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
