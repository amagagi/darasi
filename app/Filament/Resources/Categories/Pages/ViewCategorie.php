<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategorieResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategorie extends ViewRecord
{
    protected static string $resource = CategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
