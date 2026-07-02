<?php

namespace App\Filament\Resources\Cours\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CoursForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titre')
                    ->required()
                    ->maxLength(200),
                    
                Textarea::make('description')
                    ->columnSpanFull(),
                    
                Textarea::make('objectifs')
                    ->columnSpanFull(),
                    
                Textarea::make('prerequis')
                    ->columnSpanFull(),
                    
                Select::make('pole_id')
                    ->label('Pôle')
                    ->relationship('pole', 'nom')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                // ✅ SOLUTION - Même style que ton UserForm
                Select::make('formateur_id')
                    ->label('Formateur')
                    ->options(
                        User::where('role', 'formateur')
                            ->get()
                            ->mapWithKeys(fn ($user) => [
                                $user->id => $user->prenom . ' ' . $user->nom
                            ])
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Sélectionnez un formateur'),
                    
                Select::make('categorie_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                    
                Select::make('niveau_id')
                    ->label('Niveau')
                    ->relationship('niveau', 'libelle')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                    
                FileUpload::make('image_couverture')
                    ->label('Image de couverture')
                    ->image()
                    ->directory('cours/couvertures')
                    ->maxSize(2048),
                    
                TextInput::make('video_presentation')
                    ->label('Vidéo de présentation')
                    ->url()
                    ->placeholder('https://...'),
                    
                Toggle::make('est_certifiant')
                    ->label('Certifiant')
                    ->default(false),
                    
                TextInput::make('note_minimale_certificat')
                    ->label('Note minimale pour certificat')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(70)
                    ->visible(fn ($get) => $get('est_certifiant') === true),
                    
                TextInput::make('prix')
                    ->label('Prix (FCFA)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('FCFA'),
                    
                Toggle::make('est_gratuit')
                    ->label('Gratuit')
                    ->default(false),
                    
                Select::make('statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'publie' => 'Publié',
                        'archive' => 'Archivé',
                    ])
                    ->default('brouillon')
                    ->required(),
                    
                TextInput::make('note_moyenne')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),
                    
                TextInput::make('nb_apprenants')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),
                    
                DateTimePicker::make('published_at')
                    ->nullable(),
            ]);
    }
}