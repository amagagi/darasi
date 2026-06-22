<?php

namespace App\Filament\Resources\ContenuJuridiques\Pages;

use App\Filament\Resources\ContenuJuridiques\ContenuJuridiqueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContenuJuridiques extends ListRecords
{
    protected static string $resource = ContenuJuridiqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
