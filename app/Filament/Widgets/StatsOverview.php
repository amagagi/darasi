<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Cours;
use App\Models\Inscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Utilisateurs', User::count())
                ->description('Total utilisateurs')
                ->color('success'),

            Stat::make('Cours', Cours::count())
                ->description('Cours disponibles')
                ->color('primary'),

            Stat::make('Inscriptions', Inscription::count())
                ->description('Total inscriptions')
                ->color('warning'),
        ];
    }
}