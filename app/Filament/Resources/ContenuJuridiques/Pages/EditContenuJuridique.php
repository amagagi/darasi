<?php

namespace App\Filament\Resources\ContenuJuridiques\Pages;

use App\Filament\Resources\ContenuJuridiques\ContenuJuridiqueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditContenuJuridique extends EditRecord
{
    protected static string $resource = ContenuJuridiqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
