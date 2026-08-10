<?php

namespace App\Filament\Resources\Annonces\Pages;

use App\Filament\Resources\Annonces\AnnonceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnnonce extends EditRecord
{
    protected static string $resource = AnnonceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Même règle qu'à la création : publier sans date signifie maintenant.
        if (($data['est_publiee'] ?? false) && empty($data['publiee_le'])) {
            $data['publiee_le'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
