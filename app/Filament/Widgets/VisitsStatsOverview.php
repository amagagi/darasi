<?php

namespace App\Filament\Widgets;

use App\Models\SiteVisit;
use App\Models\VisitCounter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class VisitsStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total = VisitCounter::query()->orderByDesc('date')->value('total_visits') ?? 0;
        $aujourdHui = VisitCounter::query()->where('date', today())->value('today_visits') ?? 0;
        $sept = VisitCounter::query()->where('date', '>=', today()->subDays(6))->sum('today_visits');
        $trente = VisitCounter::query()->where('date', '>=', today()->subDays(29))->sum('today_visits');

        $topPages = SiteVisit::query()
            ->where('visited_at', '>=', now()->subDays(30))
            ->whereNotNull('page_url')
            ->select('page_url', DB::raw('count(*) as total'))
            ->groupBy('page_url')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'page_url');

        $descriptionTopPages = $topPages->isEmpty()
            ? 'Aucune donnée sur les 30 derniers jours'
            : $topPages->map(fn ($n, $page) => "{$page} ({$n})")->implode(' · ');

        return [
            Stat::make('👁️ Visites totales', number_format($total, 0, ',', ' '))
                ->color('primary')
                ->icon('heroicon-o-eye'),

            Stat::make('📅 Aujourd\'hui', number_format($aujourdHui, 0, ',', ' '))
                ->color('success')
                ->icon('heroicon-o-calendar'),

            Stat::make('📈 7 derniers jours', number_format($sept, 0, ',', ' '))
                ->color('info')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('📊 30 derniers jours — pages les plus vues', number_format($trente, 0, ',', ' '))
                ->description($descriptionTopPages)
                ->color('warning')
                ->icon('heroicon-o-chart-bar-square'),
        ];
    }
}
