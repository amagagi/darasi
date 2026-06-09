<?php

namespace App\Filament\Resources\AbonnementTypes\Pages;

use App\Filament\Resources\AbonnementTypes\AbonnementTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAbonnementType extends ViewRecord
{
    protected static string $resource = AbonnementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
