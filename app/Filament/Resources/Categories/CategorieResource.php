<?php

namespace App\Filament\Resources\Categories;

use App\Models\Categorie;
use App\Models\Pole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class CategorieResource extends Resource
{
    protected static ?string $model = Categorie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $pluralModelLabel = 'Catégories';

    protected static ?string $modelLabel = 'Catégorie';

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

            Forms\Components\TextInput::make('nom')
                ->label('Nom de la catégorie')
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Set $set) =>
                    $set('slug', Str::slug($state))
                ),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(100)
                ->helperText('Généré automatiquement depuis le nom')
                ->unique('categories', 'slug', ignoreRecord: true),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('ordre')
                ->label("Ordre d'affichage")
                ->numeric()
                ->default(0)
                ->integer()
                ->helperText('Plus le chiffre est petit, plus la catégorie apparaît en premier'),
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

                Tables\Columns\TextColumn::make('nom')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->defaultSort('ordre')
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
            'index' => \App\Filament\Resources\Categories\Pages\ListCategories::route('/'),
            'create' => \App\Filament\Resources\Categories\Pages\CreateCategorie::route('/create'),
            'edit' => \App\Filament\Resources\Categories\Pages\EditCategorie::route('/{record}/edit'),
        ];
    }
}