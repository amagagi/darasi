<?php

namespace App\Filament\Resources\AbonnementTypes\Pages;

use App\Filament\Resources\AbonnementTypes\AbonnementTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAbonnementType extends EditRecord
{
    protected static string $resource = AbonnementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
