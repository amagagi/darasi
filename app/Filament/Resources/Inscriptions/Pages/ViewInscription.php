<?php

namespace App\Filament\Resources\Inscriptions\Pages;

use App\Filament\Resources\Inscriptions\InscriptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInscription extends ViewRecord
{
    protected static string $resource = InscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
