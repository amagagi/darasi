<?php

namespace App\Filament\Resources\AbonnementSouscrits\Pages;

use App\Filament\Resources\AbonnementSouscrits\AbonnementSouscritResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAbonnementSouscrit extends ViewRecord
{
    protected static string $resource = AbonnementSouscritResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
