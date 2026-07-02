<?php

namespace App\Filament\Resources\ConfigTentatives\Pages;

use App\Filament\Resources\ConfigTentatives\ConfigTentativeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConfigTentative extends ViewRecord
{
    protected static string $resource = ConfigTentativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
