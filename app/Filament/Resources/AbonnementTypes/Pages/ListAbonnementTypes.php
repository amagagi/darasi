<?php

namespace App\Filament\Resources\AbonnementTypes\Pages;

use App\Filament\Resources\AbonnementTypes\AbonnementTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbonnementTypes extends ListRecords
{
    protected static string $resource = AbonnementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
