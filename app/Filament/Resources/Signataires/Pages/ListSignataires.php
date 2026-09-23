<?php

namespace App\Filament\Resources\Signataires\Pages;

use App\Filament\Resources\Signataires\SignataireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSignataires extends ListRecords
{
    protected static string $resource = SignataireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouveau signataire'),
        ];
    }
}
