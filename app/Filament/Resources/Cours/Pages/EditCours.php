<?php

namespace App\Filament\Resources\Cours\Pages;

use App\Filament\Resources\Cours\CoursResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCours extends EditRecord
{
    protected static string $resource = CoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
