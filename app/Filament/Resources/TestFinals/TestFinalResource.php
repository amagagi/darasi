<?php

namespace App\Filament\Resources\TestFinals;

use App\Models\TestFinal;
use App\Models\Cours;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class TestFinalResource extends Resource
{
    protected static ?string $model = TestFinal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Tests finaux';

    protected static ?string $pluralModelLabel = 'Tests finaux';

    protected static ?string $modelLabel = 'Test final';

    protected static ?string $slug = 'test-finals';  // ← AJOUTER CETTE LIGNE

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('cours_id')
                ->label('Cours')
                ->options(Cours::pluck('titre', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Un seul test final par cours')
                ->native(false),

            Forms\Components\TextInput::make('titre')
                ->label('Titre du test final')
                ->required()
                ->maxLength(200),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('note_minimale')
                ->label('Note minimale pour réussite (%)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->default(70)
                ->suffix('%'),

            Forms\Components\TextInput::make('duree_limite')
                ->label('Durée limite (minutes)')
                ->numeric()
                ->nullable()
                ->integer()
                ->minValue(1)
                ->suffix('min')
                ->helperText('Laisser vide pour illimité'),
        ]);
    }

    // =========================
    // INFOLIST  ← NOUVEAU
    // =========================
    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([

            \Filament\Infolists\Components\TextEntry::make('cours.titre')
                ->label('Cours')
                ->badge()
                ->color('primary'),

            \Filament\Infolists\Components\TextEntry::make('titre')
                ->label('Titre du test final')
                ->weight('bold'),

            \Filament\Infolists\Components\TextEntry::make('description')
                ->label('Description')
                ->placeholder('Aucune description')
                ->columnSpanFull(),

            \Filament\Infolists\Components\TextEntry::make('note_minimale')
                ->label('Note minimale')
                ->formatStateUsing(fn ($state) => $state . '%')
                ->badge()
                ->color('warning'),

            \Filament\Infolists\Components\TextEntry::make('duree_limite')
                ->label('Durée limite')
                ->formatStateUsing(fn ($state) => $state ? $state . ' minutes' : 'Illimité'),

            \Filament\Infolists\Components\TextEntry::make('questions_count')
                ->label('Nombre de questions')
                ->state(fn ($record) => $record->questions()->count())
                ->badge()
                ->color('success'),

            \Filament\Infolists\Components\TextEntry::make('created_at')
                ->label('Créé le')
                ->dateTime('d/m/Y H:i'),

            \Filament\Infolists\Components\TextEntry::make('updated_at')
                ->label('Modifié le')
                ->dateTime('d/m/Y H:i'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('cours.titre')
                    ->label('Cours')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Test final')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('note_minimale')
                    ->label('Note minimale')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duree_limite')
                    ->label('Durée limite')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' min' : 'Illimité')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Nb questions')
                    ->counts('questions')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('tentatives_count')
                    ->label('Tentatives')
                    ->counts('tentatives')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('cours_id')
                    ->label('Cours')
                    ->relationship('cours', 'titre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    // =========================
    // PAGES
    // =========================
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\TestFinals\Pages\ListTestFinals::route('/'),
            'create' => \App\Filament\Resources\TestFinals\Pages\CreateTestFinal::route('/create'),
            'edit' => \App\Filament\Resources\TestFinals\Pages\EditTestFinal::route('/{record}/edit'),
            'view' => \App\Filament\Resources\TestFinals\Pages\ViewTestFinal::route('/{record}'),  // ← AJOUTER
        ];
    }
}