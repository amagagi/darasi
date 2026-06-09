<?php

namespace App\Filament\Resources\TestFinals\Pages;

use App\Filament\Resources\TestFinals\TestFinalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTestFinals extends ListRecords
{
    protected static string $resource = TestFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
