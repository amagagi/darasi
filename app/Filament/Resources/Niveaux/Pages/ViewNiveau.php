<?php

namespace App\Filament\Resources\Niveaux\Pages;

use App\Filament\Resources\Niveaux\NiveauResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNiveau extends ViewRecord
{
    protected static string $resource = NiveauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
