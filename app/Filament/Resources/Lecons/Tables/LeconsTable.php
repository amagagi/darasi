<?php

namespace App\Filament\Resources\Lecons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeconsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('titre')
                    ->searchable(),
                TextColumn::make('type_contenu')
                    ->badge(),
                TextColumn::make('url_video')
                    ->searchable(),
                TextColumn::make('url_pdf')
                    ->searchable(),
                TextColumn::make('duree_video')
                    ->numeric()
                    ->sortable(),
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
