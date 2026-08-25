<?php

namespace App\Filament\Resources\Annonces;

use App\Filament\Resources\Annonces\Pages\CreateAnnonce;
use App\Filament\Resources\Annonces\Pages\EditAnnonce;
use App\Filament\Resources\Annonces\Pages\ListAnnonces;
use App\Models\Annonce;
use App\Services\ImageOptimizerService;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
// En Filament v5 les composants de mise en page vivent dans Schemas, pas Forms.
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class AnnonceResource extends Resource
{
    protected static ?string $model = Annonce::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Actualités & alertes';

    protected static ?string $pluralModelLabel = 'Actualités';

    protected static ?string $modelLabel = 'Actualité';

    protected static ?string $slug = 'annonces';

    protected static ?string $recordTitleAttribute = 'titre';

    /** Compteur des annonces actuellement visibles sur le site. */
    public static function getNavigationBadge(): ?string
    {
        $actives = Annonce::query()->active()->count();

        return $actives > 0 ? (string) $actives : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Contenu')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('titre')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder('Ex : Ouverture des inscriptions à la session de septembre'),

                    Forms\Components\Textarea::make('extrait')
                        ->label('Message court (bandeau)')
                        ->maxLength(500)
                        ->rows(2)
                        ->columnSpanFull()
                        ->helperText('Une phrase, affichée telle quelle dans le bandeau d\'alerte. Si vide, le titre est utilisé.'),

                    Forms\Components\RichEditor::make('contenu')
                        ->label('Contenu détaillé (section Actualités)')
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'link',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'blockquote',
                        ]),

                    Forms\Components\FileUpload::make('image')
                        ->label('Image d\'illustration')
                        ->image()
                        ->directory('annonces')
                        // ->visibility('public') seul ne suffit pas : sans ->disk('public')
                        // explicite, Filament utilise config('filament.default_filesystem_disk')
                        // (= FILESYSTEM_DISK, "local" par défaut), dont la racine est
                        // storage/app/private depuis Laravel 11 — invisible pour nginx/le lien
                        // symbolique public/storage, qui pointent sur storage/app/public.
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(5120) // Ko — 5 Mo
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Formats acceptés : JPG, PNG, WEBP. Taille maximale : 5 Mo. Dimensions recommandées : 1920×1080px.')
                        ->afterStateUpdated(function (?string $state): void {
                            if ($state === null) {
                                return;
                            }

                            $optimizer = app(ImageOptimizerService::class);
                            $optimizer->optimize($state);
                            $optimizer->thumbnail($state);
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Affichage')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options([
                            'info' => 'ℹ️ Information',
                            'succes' => '✅ Bonne nouvelle',
                            'avertissement' => '⚠️ Avertissement',
                            'urgent' => '🚨 Urgent',
                        ])
                        ->default('info')
                        ->required()
                        ->native(false)
                        ->helperText('Détermine la couleur du bandeau.'),

                    Forms\Components\Select::make('cible')
                        ->label('Audience')
                        ->options([
                            'tous' => 'Tout le monde',
                            'public' => 'Visiteurs non connectés',
                            'connectes' => 'Utilisateurs connectés',
                        ])
                        ->default('tous')
                        ->required()
                        ->native(false),

                    Forms\Components\Toggle::make('afficher_banniere')
                        ->label('Afficher dans le bandeau d\'alerte')
                        ->default(false)
                        ->helperText('Bandeau en haut de la page.'),

                    Forms\Components\Toggle::make('afficher_actualites')
                        ->label('Afficher dans la section Actualités')
                        ->default(true),

                    Forms\Components\Toggle::make('est_permanente')
                        ->label('Bandeau non masquable')
                        ->default(false)
                        ->helperText('À réserver aux incidents et maintenances : l\'utilisateur ne peut pas fermer le bandeau.'),

                    Forms\Components\TextInput::make('priorite')
                        ->label('Priorité')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('La valeur la plus élevée s\'affiche en premier.'),

                    Forms\Components\TextInput::make('lien_url')
                        ->label('Lien (optionnel)')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://darasihub.com/...'),

                    Forms\Components\TextInput::make('lien_libelle')
                        ->label('Libellé du lien')
                        ->maxLength(255)
                        ->placeholder('Ex : En savoir plus'),
                ]),

            Section::make('Publication')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('est_publiee')
                        ->label('Publiée')
                        ->default(false)
                        ->helperText('Tant que ce commutateur est désactivé, rien n\'est visible sur le site.'),

                    Forms\Components\DateTimePicker::make('publiee_le')
                        ->label('Publier à partir du')
                        ->seconds(false)
                        ->helperText('Vide = immédiatement.'),

                    Forms\Components\DateTimePicker::make('expire_le')
                        ->label('Expire le')
                        ->seconds(false)
                        ->after('publiee_le')
                        ->helperText('Vide = pas d\'expiration.'),
                ]),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'info' => 'Information',
                        'succes' => 'Bonne nouvelle',
                        'avertissement' => 'Avertissement',
                        'urgent' => 'Urgent',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'info' => 'info',
                        'succes' => 'success',
                        'avertissement' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),

                // Statut réel = croisement de est_publiee et des dates.
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->state(fn (Annonce $record) => static::statut($record))
                    ->color(fn (string $state) => match ($state) {
                        'En ligne' => 'success',
                        'Programmée' => 'info',
                        'Expirée' => 'gray',
                        default => 'warning',
                    }),

                Tables\Columns\IconColumn::make('afficher_banniere')
                    ->label('Bandeau')
                    ->boolean(),

                Tables\Columns\TextColumn::make('cible')
                    ->label('Audience')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'tous' => 'Tout le monde',
                        'public' => 'Visiteurs',
                        'connectes' => 'Connectés',
                        default => $state,
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('priorite')
                    ->label('Priorité')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('publiee_le')
                    ->label('Publiée le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Immédiate')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expire_le')
                    ->label('Expire le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creePar.nom')
                    ->label('Créée par')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'info' => 'Information',
                        'succes' => 'Bonne nouvelle',
                        'avertissement' => 'Avertissement',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\TernaryFilter::make('est_publiee')
                    ->label('Publiée')
                    ->trueLabel('Publiées')
                    ->falseLabel('Brouillons')
                    ->placeholder('Toutes'),

                Tables\Filters\TernaryFilter::make('afficher_banniere')
                    ->label('Bandeau')
                    ->trueLabel('Dans le bandeau')
                    ->falseLabel('Hors bandeau')
                    ->placeholder('Toutes'),
            ])
            ->recordActions([
                // Bascule publier/dépublier en un clic depuis la liste.
                \Filament\Actions\Action::make('basculerPublication')
                    ->label(fn (Annonce $record) => $record->est_publiee ? 'Dépublier' : 'Publier')
                    ->icon(fn (Annonce $record) => $record->est_publiee
                        ? Heroicon::OutlinedEyeSlash
                        : Heroicon::OutlinedPaperAirplane)
                    ->color(fn (Annonce $record) => $record->est_publiee ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Annonce $record): void {
                        $record->est_publiee = ! $record->est_publiee;

                        // Première publication : on horodate maintenant pour que
                        // l'annonce soit immédiatement visible.
                        if ($record->est_publiee && $record->publiee_le === null) {
                            $record->publiee_le = now();
                        }

                        $record->save();
                    }),

                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Statut lisible, dérivé du drapeau de publication et des dates. */
    private static function statut(Annonce $annonce): string
    {
        if (! $annonce->est_publiee) {
            return 'Brouillon';
        }

        if ($annonce->expire_le !== null && $annonce->expire_le->isPast()) {
            return 'Expirée';
        }

        if ($annonce->publiee_le !== null && $annonce->publiee_le->isFuture()) {
            return 'Programmée';
        }

        return 'En ligne';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnonces::route('/'),
            'create' => CreateAnnonce::route('/create'),
            'edit' => EditAnnonce::route('/{record}/edit'),
        ];
    }
}
