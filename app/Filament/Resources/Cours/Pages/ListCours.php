<?php

namespace App\Filament\Resources\Cours\Pages;

use App\Filament\Resources\Cours\CoursResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCours extends ListRecords
{
    protected static string $resource = CoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
