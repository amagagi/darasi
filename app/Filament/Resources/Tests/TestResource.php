<?php

namespace App\Filament\Resources\Tests;

use App\Models\Test;
use App\Models\Module;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Tests';

    protected static ?string $pluralModelLabel = 'Tests';

    protected static ?string $modelLabel = 'Test';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('module_id')
                ->label('Module')
                ->options(Module::with('cours')->get()->mapWithKeys(fn ($module) => [
                    $module->id => $module->cours->titre . ' - ' . $module->titre
                ]))
                ->searchable()
                ->preload()
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('titre')
                ->label('Titre du test')
                ->required()
                ->maxLength(200),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->default(0)
                ->integer()
                ->helperText('Plus le chiffre est petit, plus le test apparaît en premier'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('module.cours.titre')
                    ->label('Cours')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('module.titre')
                    ->label('Module')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Test')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

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
            ->defaultSort('module_id', 'ordre')
            ->filters([
                Tables\Filters\SelectFilter::make('module_id')
                    ->label('Module')
                    ->relationship('module', 'titre')
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
            'index' => \App\Filament\Resources\Tests\Pages\ListTests::route('/'),
            'create' => \App\Filament\Resources\Tests\Pages\CreateTest::route('/create'),
            'edit' => \App\Filament\Resources\Tests\Pages\EditTest::route('/{record}/edit'),
        ];
    }
}