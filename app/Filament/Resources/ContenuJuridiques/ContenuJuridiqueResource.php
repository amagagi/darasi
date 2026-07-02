<?php

namespace App\Filament\Resources\ContenuJuridiques;

use App\Models\ContenuJuridique;
use App\Filament\Resources\ContenuJuridiques\Pages\CreateContenuJuridique;
use App\Filament\Resources\ContenuJuridiques\Pages\EditContenuJuridique;
use App\Filament\Resources\ContenuJuridiques\Pages\ListContenuJuridiques;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class ContenuJuridiqueResource extends Resource
{
    protected static ?string $model = ContenuJuridique::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Contenus juridiques';

    protected static ?string $pluralModelLabel = 'Contenus juridiques';

    protected static ?string $modelLabel = 'Contenu juridique';
    
    protected static ?string $slug = 'contenus-juridiques';

    // protected static string $navigationGroup = 'Paramètres';  ← SUPPRIMÉ

    protected static ?string $recordTitleAttribute = 'titre';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('type')
                ->label('Type de contenu')
                ->options([
                    'regles_plateforme' => '📜 Règles de la plateforme',
                    'cgu' => '📋 Conditions Générales',
                    'mentions_legales' => '⚖️ Mentions légales',
                    'politique_confidentialite' => '🔒 Politique de confidentialité',
                ])
                ->required()
                ->unique(ignoreRecord: true)
                ->native(false)
                ->helperText('Un seul contenu par type'),

            Forms\Components\TextInput::make('titre')
                ->label('Titre')
                ->required()
                ->maxLength(255)
                ->placeholder('Ex: Règles de la plateforme'),

            Forms\Components\RichEditor::make('contenu')
                ->label('Contenu')
                ->required()
                ->columnSpanFull()
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                    'link',
                    'bulletList',
                    'orderedList',
                    'h2',
                    'h3',
                    'blockquote',
                ])
                ->helperText('Utilisez l\'éditeur pour mettre en forme votre contenu'),

            Forms\Components\Toggle::make('est_actif')
                ->label('Actif')
                ->default(true)
                ->helperText('Seul le contenu actif sera affiché sur le site'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'regles_plateforme' => '📜 Règles',
                        'cgu' => '📋 CGU',
                        'mentions_legales' => '⚖️ Mentions',
                        'politique_confidentialite' => '🔒 Confidentialité',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'regles_plateforme' => 'primary',
                        'cgu' => 'success',
                        'mentions_legales' => 'warning',
                        'politique_confidentialite' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_modification')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('modifiePar.nom')
                    ->label('Modifié par')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('type')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'regles_plateforme' => 'Règles de la plateforme',
                        'cgu' => 'CGU',
                        'mentions_legales' => 'Mentions légales',
                        'politique_confidentialite' => 'Politique de confidentialité',
                    ]),
                Tables\Filters\TernaryFilter::make('est_actif')
                    ->label('Actif')
                    ->trueLabel('Actif')
                    ->falseLabel('Inactif')
                    ->placeholder('Tous'),
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
            'index' => ListContenuJuridiques::route('/'),
            'create' => CreateContenuJuridique::route('/create'),
            'edit' => EditContenuJuridique::route('/{record}/edit'),
        ];
    }
}