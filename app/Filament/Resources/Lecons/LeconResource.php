<?php

namespace App\Filament\Resources\Lecons;

use App\Models\Lecon;
use App\Models\Module;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class LeconResource extends Resource
{
    protected static ?string $model = Lecon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Leçons';

    protected static ?string $pluralModelLabel = 'Leçons';

    protected static ?string $modelLabel = 'Leçon';

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
                ->label('Titre de la leçon')
                ->required()
                ->maxLength(200),

            Forms\Components\Select::make('type_contenu')
                ->label('Type de contenu')
                ->options([
                    'video' => '🎬 Vidéo',
                    'pdf' => '📄 PDF',
                    'article' => '📝 Article',
                ])
                ->required()
                ->reactive()
                ->native(false),

            // Champ pour le texte (article)
            Forms\Components\RichEditor::make('contenu_text')
                ->label('Contenu (article)')
                ->visible(fn ($get) => $get('type_contenu') === 'article')
                ->columnSpanFull(),

            // Vidéo : un seul champ, donc une seule liaison d'état. Il accepte
            // soit un lien externe (YouTube), soit un chemin de fichier sur le
            // disque privé — le contrôleur de diffusion distingue les deux et
            // ne signe que le second.
            Forms\Components\TextInput::make('url_video')
                ->label('URL de la vidéo')
                ->placeholder('https://www.youtube.com/watch?v=... ou lecons/videos/cours.mp4')
                ->visible(fn ($get) => $get('type_contenu') === 'video')
                ->helperText('Lien YouTube, ou chemin d\'un fichier déposé sur le disque privé (diffusé en streaming signé).'),

            // Champ pour le PDF
            Forms\Components\FileUpload::make('url_pdf')
                ->label('Fichier PDF')
                ->acceptedFileTypes(['application/pdf'])
                ->directory('lecons/pdfs')
                // Anciennement disque `public` : le PDF d'un cours payant était
                // téléchargeable par simple devinette d'URL, sans compte.
                ->disk(\App\Models\Lecon::DISQUE_PRIVE)
                ->visibility('private')
                ->maxSize(64 * 1024)
                ->visible(fn ($get) => $get('type_contenu') === 'pdf'),

            // Durée de la vidéo
            Forms\Components\TextInput::make('duree_video')
                ->label('Durée de la vidéo (secondes)')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->visible(fn ($get) => $get('type_contenu') === 'video')
                ->helperText('Exemple: 600 = 10 minutes'),

            Forms\Components\TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->required()
                ->default(0)
                ->integer()
                ->helperText('Plus le chiffre est petit, plus la leçon apparaît en premier'),
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
                    ->label('Leçon')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type_contenu')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'video' => '🎬 Vidéo',
                        'pdf' => '📄 PDF',
                        'article' => '📝 Article',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'video' => 'danger',
                        'pdf' => 'warning',
                        'article' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('duree_video')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('i:s', $state) : '-')
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

                Tables\Filters\SelectFilter::make('type_contenu')
                    ->label('Type de contenu')
                    ->options([
                        'video' => 'Vidéo',
                        'pdf' => 'PDF',
                        'article' => 'Article',
                    ]),
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
            'index' => \App\Filament\Resources\Lecons\Pages\ListLecons::route('/'),
            'create' => \App\Filament\Resources\Lecons\Pages\CreateLecon::route('/create'),
            'edit' => \App\Filament\Resources\Lecons\Pages\EditLecon::route('/{record}/edit'),
        ];
    }
}