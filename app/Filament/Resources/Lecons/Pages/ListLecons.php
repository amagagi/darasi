<?php

namespace App\Filament\Resources\Lecons\Pages;

use App\Filament\Resources\Lecons\LeconResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLecons extends ListRecords
{
    protected static string $resource = LeconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
