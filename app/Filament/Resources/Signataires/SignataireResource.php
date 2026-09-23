<?php

namespace App\Filament\Resources\Signataires;

use App\Filament\Resources\Signataires\Pages\CreateSignataire;
use App\Filament\Resources\Signataires\Pages\EditSignataire;
use App\Filament\Resources\Signataires\Pages\ListSignataires;
use App\Models\Signataire;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Signataires apposés sur les certificats.
 *
 * Modifier un signataire n'altère pas les certificats déjà délivrés : leurs
 * signatures sont figées à l'émission (voir CertificatService).
 */
class SignataireResource extends Resource
{
    protected static ?string $model = Signataire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = 'Signataires';

    protected static ?string $pluralModelLabel = 'Signataires';

    protected static ?string $modelLabel = 'Signataire';

    protected static ?string $slug = 'signataires';

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Identité')
                ->description('Imprimés sous la signature, tels que saisis.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nom')
                        ->label('Nom complet')
                        ->required()
                        ->maxLength(150)
                        ->placeholder('Ex : Dr Moussa Garba'),

                    Forms\Components\TextInput::make('fonction')
                        ->label('Fonction')
                        ->required()
                        ->maxLength(150)
                        ->placeholder('Ex : Directeur général'),
                ]),

            Section::make('Signature')
                ->schema([
                    Forms\Components\FileUpload::make('image_signature')
                        ->label('Image de la signature')
                        ->image()
                        // Disque privé : jamais servie publiquement, elle ne
                        // se lit que dans les certificats générés.
                        ->disk(Signataire::DISQUE)
                        ->directory('signataires')
                        ->visibility('private')
                        ->acceptedFileTypes(['image/png', 'image/jpeg'])
                        ->maxSize(1024) // Ko
                        ->imagePreviewHeight('120')
                        ->helperText('PNG à fond transparent recommandé, 1 Mo maximum. Sans image, une ligne reste vierge pour signer à la main.'),
                ]),

            Section::make('Utilisation')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('est_actif')
                        ->label('Signataire par défaut')
                        ->helperText('Signe les certificats des cours qui n\'ont pas de signataires propres (3 au maximum).')
                        ->default(true),

                    Forms\Components\TextInput::make('ordre')
                        ->label('Ordre d\'impression')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('De gauche à droite. Réordonnable aussi par glisser-déposer dans la liste.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_signature')
                    ->label('Signature')
                    ->disk(Signataire::DISQUE)
                    ->visibility('private'),

                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fonction')
                    ->label('Fonction')
                    ->searchable(),

                Tables\Columns\IconColumn::make('est_actif')
                    ->label('Par défaut')
                    ->boolean(),

                Tables\Columns\TextColumn::make('cours_count')
                    ->label('Cours dédiés')
                    ->counts('cours')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ordre')
            ->reorderable('ordre')
            ->filters([
                Tables\Filters\TernaryFilter::make('est_actif')
                    ->label('Par défaut')
                    ->trueLabel('Par défaut')
                    ->falseLabel('Cours dédiés uniquement')
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
            'index' => ListSignataires::route('/'),
            'create' => CreateSignataire::route('/create'),
            'edit' => EditSignataire::route('/{record}/edit'),
        ];
    }
}
