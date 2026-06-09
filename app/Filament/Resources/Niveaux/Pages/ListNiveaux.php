<?php

namespace App\Filament\Resources\Niveaux\Pages;

use App\Filament\Resources\Niveaux\NiveauResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNiveaux extends ListRecords
{
    protected static string $resource = NiveauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
