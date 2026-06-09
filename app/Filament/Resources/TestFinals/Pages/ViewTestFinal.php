<?php

namespace App\Filament\Resources\TestFinals\Pages;

use App\Filament\Resources\TestFinals\TestFinalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTestFinal extends ViewRecord
{
    protected static string $resource = TestFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
