<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\InscriptionsChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\LatestInscriptions::class,
        ];
    }
    // ✅ Correction : enlève 'string' du type de retour
    public function getColumns(): int | array
    {
        return 2;
    }
}