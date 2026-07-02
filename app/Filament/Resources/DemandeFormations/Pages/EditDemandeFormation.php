<?php

namespace App\Filament\Resources\DemandeFormations\Pages;

use App\Filament\Resources\DemandeFormations\DemandeFormationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDemandeFormation extends EditRecord
{
    protected static string $resource = DemandeFormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
