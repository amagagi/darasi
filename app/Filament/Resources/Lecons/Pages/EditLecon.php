<?php

namespace App\Filament\Resources\Lecons\Pages;

use App\Filament\Resources\Lecons\LeconResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLecon extends EditRecord
{
    protected static string $resource = LeconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
