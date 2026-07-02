<?php

namespace App\Filament\Resources\Certificats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inscription_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tentative_final_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('code_verification')
                    ->searchable(),
                TextColumn::make('url_pdf')
                    ->searchable(),
                TextColumn::make('date_emission')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('est_valide')
                    ->boolean(),
                TextColumn::make('date_revocation')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('revoque_par')
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
