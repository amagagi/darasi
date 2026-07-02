<?php

namespace App\Filament\Widgets;

use App\Models\Inscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestInscriptions extends BaseWidget
{
    protected static ?string $heading = 'Dernières inscriptions';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inscription::query()
                    ->with(['apprenant', 'cours'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('apprenant.prenom')
                    ->label('Apprenant')
                    ->formatStateUsing(fn ($record) => $record->apprenant?->prenom . ' ' . $record->apprenant?->nom)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cours.titre')
                    ->label('Cours')
                    ->searchable(),
                Tables\Columns\TextColumn::make('progression')
                    ->label('Progression')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}