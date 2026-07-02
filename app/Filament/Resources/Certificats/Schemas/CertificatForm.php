<?php

namespace App\Filament\Resources\Certificats\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CertificatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('inscription_id')
                    ->required()
                    ->numeric(),
                TextInput::make('tentative_final_id')
                    ->required()
                    ->numeric(),
                TextInput::make('code_verification')
                    ->required(),
                TextInput::make('url_pdf'),
                DateTimePicker::make('date_emission')
                    ->required(),
                Toggle::make('est_valide')
                    ->required(),
                DateTimePicker::make('date_revocation'),
                TextInput::make('revoque_par')
                    ->numeric(),
                Textarea::make('motif_revocation')
                    ->columnSpanFull(),
            ]);
    }
}
