<?php

namespace App\Filament\Resources\Signataires\Pages;

use App\Filament\Resources\Signataires\SignataireResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSignataire extends EditRecord
{
    protected static string $resource = SignataireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
