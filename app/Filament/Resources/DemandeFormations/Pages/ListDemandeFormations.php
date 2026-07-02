<?php

namespace App\Filament\Resources\DemandeFormations\Pages;

use App\Filament\Resources\DemandeFormations\DemandeFormationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDemandeFormations extends ListRecords
{
    protected static string $resource = DemandeFormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
