<?php

namespace App\Filament\Resources\ConfigTentatives\Pages;

use App\Filament\Resources\ConfigTentatives\ConfigTentativeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConfigTentatives extends ListRecords
{
    protected static string $resource = ConfigTentativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
