<?php

namespace App\Filament\Resources\Certificats\Pages;

use App\Filament\Resources\Certificats\CertificatResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCertificat extends ViewRecord
{
    protected static string $resource = CertificatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
