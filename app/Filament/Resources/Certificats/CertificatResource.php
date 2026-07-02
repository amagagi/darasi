<?php

namespace App\Filament\Resources\Certificats;

use App\Models\Certificat;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class CertificatResource extends Resource
{
    protected static ?string $model = Certificat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $navigationLabel = 'Certificats';

    protected static ?string $pluralModelLabel = 'Certificats';

    protected static ?string $modelLabel = 'Certificat';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('inscription_id')
                ->label('Inscription')
                ->relationship('inscription', 'id')
                ->searchable()
                ->disabled()
                ->dehydrated(false),

            Forms\Components\Select::make('tentative_final_id')
                ->label('Tentative finale')
                ->relationship('tentativeFinal', 'id')
                ->searchable()
                ->disabled()
                ->dehydrated(false),

            Forms\Components\TextInput::make('code_verification')
                ->label('Code de vérification')
                ->disabled()
                ->copyable(),

            Forms\Components\TextInput::make('url_pdf')
                ->label('URL du PDF')
                ->url()
                ->disabled()
                ->copyable(),

            Forms\Components\DateTimePicker::make('date_emission')
                ->label("Date d'émission")
                ->disabled(),

            Forms\Components\Toggle::make('est_valide')
                ->label('Valide')
                ->disabled(),

            Forms\Components\Select::make('revoque_par')
                ->label('Révoqué par')
                ->options(User::pluck('nom', 'id'))
                ->disabled()
                ->dehydrated(false),

            Forms\Components\DateTimePicker::make('date_revocation')
                ->label('Date de révocation')
                ->disabled(),

            Forms\Components\Textarea::make('motif_revocation')
                ->label('Motif de révocation')
                ->rows(2)
                ->disabled(),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('inscription.apprenant.prenom')
                    ->label('Apprenant')
                    ->formatStateUsing(fn ($record) => 
                        $record->inscription?->apprenant?->prenom . ' ' . $record->inscription?->apprenant?->nom
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('inscription.cours.titre')
                    ->label('Cours')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('code_verification')
                    ->label('Code')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('date_emission')
                    ->label('Émis le')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('est_valide')
                    ->label('Valide')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_revocation')
                    ->label('Révoqué le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('revoquePar.nom')
                    ->label('Révoqué par')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('est_valide')
                    ->label('Statut')
                    ->options([
                        '1' => 'Valides',
                        '0' => 'Révoqués',
                    ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('revoke')
                    ->label('Révoquer')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->modalHeading('Révoquer le certificat')
                    ->modalDescription('Cette action est irréversible. Êtes-vous sûr de vouloir révoquer ce certificat ?')
                    ->form([
                        Forms\Components\Textarea::make('motif_revocation')
                            ->label('Motif de révocation')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->est_valide)
                    ->action(function ($record, array $data) {
                        $record->update([
                            'est_valide' => false,
                            'date_revocation' => now(),
                            'revoque_par' => auth()->id(),
                            'motif_revocation' => $data['motif_revocation'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Certificat révoqué')
                            ->success()
                            ->send();
                    }),

                    \Filament\Actions\Action::make('view_pdf')
                    ->label('Voir PDF')
                    ->color('primary')
                    ->icon('heroicon-o-document')
                    ->url(fn ($record) => $record->url_pdf, shouldOpenInNewTab: true)
                    ->visible(fn ($record) => $record->url_pdf),

                    \Filament\Actions\ViewAction::make(),
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
            'index' => \App\Filament\Resources\Certificats\Pages\ListCertificats::route('/'),
            'create' => \App\Filament\Resources\Certificats\Pages\CreateCertificat::route('/create'),
            'edit' => \App\Filament\Resources\Certificats\Pages\EditCertificat::route('/{record}/edit'),
        ];
    }
}