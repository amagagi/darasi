<?php

namespace App\Filament\Resources\ContenuJuridiques\Pages;

use App\Filament\Resources\ContenuJuridiques\ContenuJuridiqueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContenuJuridique extends ViewRecord
{
    protected static string $resource = ContenuJuridiqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
