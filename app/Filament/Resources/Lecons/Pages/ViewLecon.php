<?php

namespace App\Filament\Resources\Lecons\Pages;

use App\Filament\Resources\Lecons\LeconResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLecon extends ViewRecord
{
    protected static string $resource = LeconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
