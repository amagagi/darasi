<?php

namespace App\Filament\Resources\ConversationMessages\Pages;

use App\Filament\Resources\ConversationMessages\ConversationMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListConversationMessages extends ListRecords
{
    protected static string $resource = ConversationMessageResource::class;

    /** Aucune action d'en-tête : la supervision est en lecture seule. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
