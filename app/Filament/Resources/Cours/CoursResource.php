<?php

namespace App\Filament\Resources\Cours;

use App\Models\Cours;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CoursExport;
use Filament\Actions\Action;  
class CoursResource extends Resource
{
    protected static ?string $model = Cours::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Cours';

    protected static ?string $pluralModelLabel = 'Cours';

    protected static ?string $modelLabel = 'Cours';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\TextInput::make('titre')
                ->required()
                ->maxLength(200),

            Forms\Components\Textarea::make('description')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('objectifs')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('prerequis')
                ->columnSpanFull(),

            // ✅ Select pour formateur
            Forms\Components\Select::make('formateur_id')
                ->label('Formateur')
                ->options(
                    User::where('role', 'formateur')
                        ->get()
                        ->mapWithKeys(fn ($user) => [
                            $user->id => $user->prenom . ' ' . $user->nom
                        ])
                )
                ->searchable()
                ->preload()
                ->required()
                ->placeholder('Sélectionnez un formateur'),

            Forms\Components\Select::make('pole_id')
                ->relationship('pole', 'nom')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('categorie_id')
                ->relationship('categorie', 'nom')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('niveau_id')
                ->relationship('niveau', 'libelle')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\FileUpload::make('image_couverture')
                ->image()
                ->directory('cours/images')
                ->disk('public')
                ->visibility('public')
                ->imagePreviewHeight('150'),

            Forms\Components\FileUpload::make('video_presentation')
                ->directory('cours/videos')
                ->disk('public')
                ->visibility('public')
                ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg']),

            Forms\Components\Toggle::make('est_certifiant')
                ->label('Certifiant')
                ->helperText('Ce cours délivre un certificat de réussite')
                ->default(false)
                ->reactive(),

            Forms\Components\TextInput::make('note_minimale_certificat')
                ->label('Note minimale pour certificat (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(70)
                ->step(5)
                ->suffix('%')
                ->visible(fn ($get) => $get('est_certifiant') === true)
                ->required(fn ($get) => $get('est_certifiant') === true),

            Forms\Components\Toggle::make('est_gratuit')
                ->label('Gratuit')
                ->helperText('Ce cours est accessible gratuitement')
                ->default(false)
                ->reactive(),

            Forms\Components\TextInput::make('prix')
                ->label('Prix (FCFA)')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('FCFA')
                ->disabled(fn ($get) => $get('est_gratuit') === true)
                ->helperText(fn ($get) => $get('est_gratuit') === true ? 'Gratuit : prix automatiquement à 0' : 'Entrez le prix du cours')
                ->required(fn ($get) => $get('est_gratuit') !== true)
                ->dehydrated(fn ($get) => $get('est_gratuit') !== true),

            // ✅ Ajout du champ statut (manquant)
            Forms\Components\Select::make('statut')
                ->label('Statut')
                ->options([
                    'brouillon' => 'Brouillon',
                    'publie' => 'Publié',
                    'archive' => 'Archivé',
                ])
                ->default('brouillon')
                ->required()
                ->native(false),

            Forms\Components\Repeater::make('autorisationsCorrection')
            ->label('Formateurs autorisés à corriger ce cours')
            ->relationship('autorisationsCorrection')
            ->schema([
                Forms\Components\Select::make('formateur_id')
                    ->label('Formateur')
                    ->options(User::where('role', 'formateur')->pluck('nom', 'id'))
                    ->searchable()
                    ->required(),
                    
                Forms\Components\Toggle::make('est_active')
                    ->label('Autorisation active')
                    ->default(true),
            ])
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                $data['autorise_par'] = auth()->id(); // L'admin connecté
                $data['date_autorisation'] = now();
                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                $data['autorise_par'] = auth()->id();
                $data['date_autorisation'] = now();
                return $data;
            })
            ->columnSpanFull()
            ->visible(fn () => auth()->user()->role === 'admin'),

            Forms\Components\TextInput::make('note_moyenne')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(false)
                ->visible(fn ($get, ?Cours $record) => $record !== null),

            Forms\Components\TextInput::make('nb_apprenants')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(false)
                ->visible(fn ($get, ?Cours $record) => $record !== null),

            Forms\Components\DateTimePicker::make('published_at'),
        ]);

    }
    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('export_excel')
                    ->label('📊 Exporter Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(new CoursExport(), 'cours.xlsx');
                    }),
                Action::make('export_pdf')
                    ->label('📄 Exporter PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->action(function () {
                        $cours = \App\Models\Cours::with(['formateur', 'categorie'])->get();
                        $pdf = Pdf::loadView('exports.cours-pdf', compact('cours'));
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'cours.pdf');
                    }),
            ])
            ->columns([

                Tables\Columns\TextColumn::make('titre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formateur_id')
                    ->label('Formateur')
                    ->formatStateUsing(fn ($record) => $record->formateur?->prenom . ' ' . $record->formateur?->nom)
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('formateur', function ($q) use ($search) {
                            $q->where('nom', 'like', "%{$search}%")
                              ->orWhere('prenom', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('pole.nom')
                    ->label('Pôle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('categorie.nom')
                    ->label('Catégorie')
                    ->sortable(),

                Tables\Columns\TextColumn::make('niveau.libelle')
                    ->label('Niveau')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image_couverture')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\IconColumn::make('est_certifiant')
                    ->boolean(),

                Tables\Columns\TextColumn::make('prix')
                    ->money('XOF', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->colors([
                        'warning' => 'brouillon',
                        'success' => 'publie',
                        'danger' => 'archive',
                    ]),

                Tables\Columns\TextColumn::make('autorisations_correction')
                ->label('Correcteurs autorisés')
                ->formatStateUsing(fn ($record) => 
                    $record->autorisationsCorrection
                        ->where('est_active', true)
                        ->pluck('formateur.nom')
                        ->implode(', ') ?: '-'
                )
                ->toggleable()
                ->visible(fn () => auth()->user()->role === 'admin'), // ✅ Seul admin voit cette colonne

                Tables\Columns\TextColumn::make('nb_apprenants')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'publie' => 'Publié',
                        'archive' => 'Archivé',
                    ]),
                Tables\Filters\SelectFilter::make('pole_id')
                    ->relationship('pole', 'nom')
                    ->label('Pôle'),
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
            'index' => \App\Filament\Resources\Cours\Pages\ListCours::route('/'),
            'create' => \App\Filament\Resources\Cours\Pages\CreateCours::route('/create'),
            'edit' => \App\Filament\Resources\Cours\Pages\EditCours::route('/{record}/edit'),
        ];
    }
}