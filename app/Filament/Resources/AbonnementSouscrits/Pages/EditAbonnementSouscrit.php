<?php

namespace App\Filament\Resources\AbonnementSouscrits\Pages;

use App\Filament\Resources\AbonnementSouscrits\AbonnementSouscritResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAbonnementSouscrit extends EditRecord
{
    protected static string $resource = AbonnementSouscritResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
