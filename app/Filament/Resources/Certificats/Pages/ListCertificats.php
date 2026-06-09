<?php

namespace App\Filament\Resources\Certificats\Pages;

use App\Filament\Resources\Certificats\CertificatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCertificats extends ListRecords
{
    protected static string $resource = CertificatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
