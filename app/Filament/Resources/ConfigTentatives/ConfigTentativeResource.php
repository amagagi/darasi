<?php

namespace App\Filament\Resources\ConfigTentatives;

use App\Models\ConfigTentative;
use App\Models\Test;
use App\Models\TestFinal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class ConfigTentativeResource extends Resource
{
    protected static ?string $model = ConfigTentative::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Règles tentatives';

    protected static ?string $pluralModelLabel = 'Configurations tentatives';

    protected static ?string $modelLabel = 'Configuration';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('test_id')
                ->label('Test de module')
                ->options(Test::with('module.cours')->get()->mapWithKeys(fn ($test) => [
                    $test->id => $test->module->cours->titre . ' - ' . $test->module->titre . ' - ' . $test->titre
                ]))
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->helperText('Ou laisser vide pour un test final'),

            Forms\Components\Select::make('test_final_id')
                ->label('Test final')
                ->options(TestFinal::with('cours')->get()->mapWithKeys(fn ($test) => [
                    $test->id => $test->cours->titre . ' - ' . $test->titre
                ]))
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->helperText('Ou laisser vide pour un test de module'),

            Forms\Components\TextInput::make('max_tentatives')
                ->label('Nombre maximum de tentatives')
                ->numeric()
                ->required()
                ->default(3)
                ->minValue(1)
                ->maxValue(10)
                ->suffix('tentatives'),

            Forms\Components\TextInput::make('delai_heures')
                ->label('Délai entre deux tentatives')
                ->numeric()
                ->required()
                ->default(24)
                ->minValue(0)
                ->maxValue(720)
                ->suffix('heures')
                ->helperText('Délai avant de pouvoir retenter le test (0 = immédiat)'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('test.module.cours.titre')
                    ->label('Cours')
                    ->formatStateUsing(fn ($record) => 
                        $record->test ? $record->test->module->cours->titre : 
                        ($record->testFinal ? $record->testFinal->cours->titre : '-')
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('test.titre')
                    ->label('Test module')
                    ->formatStateUsing(fn ($record) => $record->test?->titre ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('testFinal.titre')
                    ->label('Test final')
                    ->formatStateUsing(fn ($record) => $record->testFinal?->titre ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('max_tentatives')
                    ->label('Max tentatives')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('delai_heures')
                    ->label('Délai')
                    ->formatStateUsing(fn ($state) => $state . ' heures')
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
                Tables\Filters\SelectFilter::make('test_id')
                    ->label('Test module')
                    ->relationship('test', 'titre'),
                Tables\Filters\SelectFilter::make('test_final_id')
                    ->label('Test final')
                    ->relationship('testFinal', 'titre'),
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
            'index' => \App\Filament\Resources\ConfigTentatives\Pages\ListConfigTentatives::route('/'),
            'create' => \App\Filament\Resources\ConfigTentatives\Pages\CreateConfigTentative::route('/create'),
            'edit' => \App\Filament\Resources\ConfigTentatives\Pages\EditConfigTentative::route('/{record}/edit'),
        ];
    }
}