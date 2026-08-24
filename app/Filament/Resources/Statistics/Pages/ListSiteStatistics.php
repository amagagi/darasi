<?php

namespace App\Filament\Resources\Statistics\Pages;

use App\Filament\Resources\Statistics\SiteStatisticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteStatistics extends ListRecords
{
    protected static string $resource = SiteStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle statistique'),
        ];
    }
}
