<?php

namespace App\Filament\Resources\DemandeFormations\Pages;

use App\Filament\Resources\DemandeFormations\DemandeFormationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDemandeFormation extends ViewRecord
{
    protected static string $resource = DemandeFormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
