<?php

namespace App\Filament\Resources\Statistics;

use App\Filament\Resources\Statistics\Pages\CreateSiteStatistic;
use App\Filament\Resources\Statistics\Pages\EditSiteStatistic;
use App\Filament\Resources\Statistics\Pages\ListSiteStatistics;
use App\Models\SiteStatistic;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class SiteStatisticResource extends Resource
{
    protected static ?string $model = SiteStatistic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Statistiques / Chiffres clés';

    protected static ?string $pluralModelLabel = 'Statistiques';

    protected static ?string $modelLabel = 'Statistique';

    protected static ?string $slug = 'site-statistics';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('label')
                ->label('Libellé')
                ->required()
                ->maxLength(255)
                ->placeholder('Ex : Apprenants formés')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('value')
                ->label('Valeur')
                ->required()
                ->maxLength(50)
                ->placeholder('Ex : 1200+, 98%, 15')
                ->helperText('Chaîne libre : les suffixes ("+", "%") sont acceptés tels quels.'),

            Forms\Components\Select::make('icon')
                ->label('Icône (optionnelle)')
                ->options([
                    'heroicon-o-users' => 'Utilisateurs',
                    'heroicon-o-academic-cap' => 'Formation',
                    'heroicon-o-star' => 'Étoile',
                    'heroicon-o-calendar' => 'Calendrier',
                    'heroicon-o-trophy' => 'Trophée',
                    'heroicon-o-check-badge' => 'Badge de validation',
                    'heroicon-o-globe-alt' => 'Globe',
                ])
                ->native(false),

            Forms\Components\TextInput::make('display_order')
                ->label('Ordre d\'affichage')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->helperText('Réordonnable aussi par glisser-déposer dans la liste.'),

            Forms\Components\Toggle::make('is_active')
                ->label('Visible sur le site')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visible')
                    ->trueLabel('Visibles')
                    ->falseLabel('Masqués')
                    ->placeholder('Tous'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteStatistics::route('/'),
            'create' => CreateSiteStatistic::route('/create'),
            'edit' => EditSiteStatistic::route('/{record}/edit'),
        ];
    }
}
