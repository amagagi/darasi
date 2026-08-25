<?php

namespace App\Filament\Resources\Cours\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('titre')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                TextColumn::make('formateur.nom')
                    ->label('Formateur')
                    ->searchable(),

                TextColumn::make('pole.nom')
                    ->label('Pôle'),

                TextColumn::make('categorie.nom')
                    ->label('Catégorie')
                    ->toggleable(),

                TextColumn::make('niveau.libelle')
                    ->label('Niveau')
                    ->toggleable(),

                ImageColumn::make('image_couverture')
                    ->label('Image')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('prix')
                    ->money('XOF', true)
                    ->sortable(),

                IconColumn::make('est_gratuit')
                    ->boolean(),

                IconColumn::make('est_certifiant')
                    ->boolean(),

                TextColumn::make('statut')
                    ->badge()
                    ->colors([
                        'warning' => 'brouillon',
                        'success' => 'publie',
                        'danger' => 'archive',
                    ])
                    ->sortable(),

                TextColumn::make('note_moyenne')
                    ->sortable(),

                TextColumn::make('nb_apprenants')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'publie' => 'Publié',
                        'archive' => 'Archivé',
                    ]),

                SelectFilter::make('est_gratuit')
                    ->options([
                        1 => 'Gratuit',
                        0 => 'Payant',
                    ]),

                SelectFilter::make('est_certifiant')
                    ->options([
                        1 => 'Certifiant',
                        0 => 'Non certifiant',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}