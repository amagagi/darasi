<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tableau de bord';
    
    protected static ?string $navigationLabel = 'Tableau de bord';
    
    protected static ?string $breadcrumb = 'Tableau de bord';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\NotificationsWidget::class,  // ← AJOUTER EN PREMIER
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
    
    public function getColumns(): int | array
    {
        return 2;
    }
}