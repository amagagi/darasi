<?php

namespace App\Filament\Resources\Modules;

use App\Models\Module;
use App\Models\Cours;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Modules';

    protected static ?string $pluralModelLabel = 'Modules';

    protected static ?string $modelLabel = 'Module';

    // FORM
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            // Select pour le cours
            Forms\Components\Select::make('cours_id')
                ->label('Cours')
                ->options(Cours::pluck('titre', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            
            // Titre du module
            Forms\Components\TextInput::make('titre')
                ->label('Titre du module')
                ->required()
                ->maxLength(200),
            
            // Description
            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
            
            // Ordre
            Forms\Components\TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->required()
                ->default(0)
                ->integer(),
            
            // Durée estimée
            Forms\Components\TextInput::make('duree_estimee')
                ->label('Durée estimée (minutes)')
                ->numeric()
                ->integer()
                ->default(0)
                ->suffix('min'),
        ]);
    }

    // TABLE
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cours.titre')
                    ->label('Cours')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('titre')
                    ->label('Module')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('lecons_count')
                    ->label('Nb leçons')
                    ->counts('lecons')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('duree_estimee')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' min' : '-')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('cours_id', 'ordre')
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

    // PAGES
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Modules\Pages\ListModules::route('/'),
            'create' => \App\Filament\Resources\Modules\Pages\CreateModule::route('/create'),
            'edit' => \App\Filament\Resources\Modules\Pages\EditModule::route('/{record}/edit'),
        ];
    }
}