<?php

namespace App\Filament\Resources\TestFinals\Pages;

use App\Filament\Resources\TestFinals\TestFinalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTestFinal extends EditRecord
{
    protected static string $resource = TestFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
