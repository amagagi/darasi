<?php

namespace App\Filament\Resources\Certificats\Pages;

use App\Filament\Resources\Certificats\CertificatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCertificat extends EditRecord
{
    protected static string $resource = CertificatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
