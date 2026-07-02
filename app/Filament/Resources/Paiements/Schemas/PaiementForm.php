<?php

namespace App\Filament\Resources\Paiements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaiementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('apprenant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('cours_id')
                    ->numeric(),
                TextInput::make('abonnement_type_id')
                    ->numeric(),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_komipay'),
                TextInput::make('transaction_id'),
                TextInput::make('tentatives')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('erreur_message')
                    ->columnSpanFull(),
                TextInput::make('code_validation'),
                Select::make('statut')
                    ->options([
            'en_attente' => 'En attente',
            'paye' => 'Paye',
            'echoue' => 'Echoue',
            'rembourse' => 'Rembourse',
        ])
                    ->default('en_attente')
                    ->required(),
                Select::make('mode_paiement')
                    ->options([
            'AMANATA' => 'A m a n a t a',
            'MY_NITA' => 'M y  n i t a',
            'CARTE' => 'C a r t e',
            'AIRTEL_MONEY' => 'A i r t e l  m o n e y',
            'CREDIT_CARD' => 'C r e d i t  c a r d',
        ])
                    ->required(),
                DateTimePicker::make('date_paiement'),
            ]);
    }
}
