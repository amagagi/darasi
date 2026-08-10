<?php

namespace App\Filament\Resources\ContenusSite\Pages;

use App\Filament\Resources\ContenusSite\ContenuSiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContenusSite extends ListRecords
{
    protected static string $resource = ContenuSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouveau bloc'),
        ];
    }
}
