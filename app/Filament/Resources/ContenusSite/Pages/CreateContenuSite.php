<?php

namespace App\Filament\Resources\ContenusSite\Pages;

use App\Filament\Resources\ContenusSite\ContenuSiteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContenuSite extends CreateRecord
{
    protected static string $resource = ContenuSiteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['modifie_par'] = auth()->id();

        return $data;
    }
}
