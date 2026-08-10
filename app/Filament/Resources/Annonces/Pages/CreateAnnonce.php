<?php

namespace App\Filament\Resources\Annonces\Pages;

use App\Filament\Resources\Annonces\AnnonceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnonce extends CreateRecord
{
    protected static string $resource = AnnonceResource::class;

    /** Trace l'auteur sans exposer de champ dans le formulaire. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['cree_par'] = auth()->id();

        // Publication immédiate demandée sans date : on horodate maintenant,
        // sinon l'annonce resterait invisible côté API.
        if (($data['est_publiee'] ?? false) && empty($data['publiee_le'])) {
            $data['publiee_le'] = now();
        }

        return $data;
    }
}
