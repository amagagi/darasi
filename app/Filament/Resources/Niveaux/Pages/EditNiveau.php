<?php

namespace App\Filament\Resources\Niveaux\Pages;

use App\Filament\Resources\Niveaux\NiveauResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNiveau extends EditRecord
{
    protected static string $resource = NiveauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
