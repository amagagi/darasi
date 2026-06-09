<?php

namespace App\Filament\Resources\AbonnementSouscrits\Pages;

use App\Filament\Resources\AbonnementSouscrits\AbonnementSouscritResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbonnementSouscrits extends ListRecords
{
    protected static string $resource = AbonnementSouscritResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
