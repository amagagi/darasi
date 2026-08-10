<?php

namespace App\Filament\Resources\Annonces\Pages;

use App\Filament\Resources\Annonces\AnnonceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnnonces extends ListRecords
{
    protected static string $resource = AnnonceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle actualité'),
        ];
    }
}
