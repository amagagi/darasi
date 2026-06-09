<?php

namespace App\Filament\Resources\Paiements\Pages;

use App\Filament\Resources\Paiements\PaiementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaiement extends ViewRecord
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
