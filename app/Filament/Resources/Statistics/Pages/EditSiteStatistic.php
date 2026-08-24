<?php

namespace App\Filament\Resources\Statistics\Pages;

use App\Filament\Resources\Statistics\SiteStatisticResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteStatistic extends EditRecord
{
    protected static string $resource = SiteStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
