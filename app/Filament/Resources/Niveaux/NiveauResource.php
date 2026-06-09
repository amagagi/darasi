<?php

namespace App\Filament\Resources\Niveaux;

use App\Models\Niveau;
use App\Models\Pole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class NiveauResource extends Resource
{
    protected static ?string $model = Niveau::class;

    // ✅ Correction : utiliser OutlinedChartBar
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Niveaux';

    protected static ?string $pluralModelLabel = 'Niveaux';

    protected static ?string $modelLabel = 'Niveau';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('pole_id')
                ->label('Pôle')
                ->options(Pole::pluck('nom', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('libelle')
                ->label('Libellé du niveau')
                ->required()
                ->maxLength(100),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('ordre')
                ->label("Ordre d'affichage")
                ->numeric()
                ->default(0)
                ->integer()
                ->helperText('Plus le chiffre est petit, plus le niveau apparaît en premier'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('pole.nom')
                    ->label('Pôle')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('libelle')
                    ->label('Niveau')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('cours_count')
                    ->label('Nb cours')
                    ->counts('cours')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('pole_id', 'ordre')
            ->filters([
                Tables\Filters\SelectFilter::make('pole_id')
                    ->label('Pôle')
                    ->relationship('pole', 'nom')
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
            'index' => \App\Filament\Resources\Niveaux\Pages\ListNiveaux::route('/'),
            'create' => \App\Filament\Resources\Niveaux\Pages\CreateNiveau::route('/create'),
            'edit' => \App\Filament\Resources\Niveaux\Pages\EditNiveau::route('/{record}/edit'),
        ];
    }
}