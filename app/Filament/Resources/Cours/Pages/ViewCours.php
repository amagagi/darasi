<?php

namespace App\Filament\Resources\Cours\Pages;

use App\Filament\Resources\Cours\CoursResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCours extends ViewRecord
{
    protected static string $resource = CoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
