<?php

namespace App\Filament\Resources\ConfigTentatives\Pages;

use App\Filament\Resources\ConfigTentatives\ConfigTentativeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditConfigTentative extends EditRecord
{
    protected static string $resource = ConfigTentativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
