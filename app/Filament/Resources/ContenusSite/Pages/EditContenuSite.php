<?php

namespace App\Filament\Resources\ContenusSite\Pages;

use App\Filament\Resources\ContenusSite\ContenuSiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContenuSite extends EditRecord
{
    protected static string $resource = ContenuSiteResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['modifie_par'] = auth()->id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
