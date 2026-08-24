<?php

namespace App\Filament\Widgets;

use App\Models\VisitCounter;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class VisitsChart extends ChartWidget
{
    // $heading doit être non statique
    protected ?string $heading = 'Évolution des visites (30 derniers jours)';

    // $sort doit être statique
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Lu depuis l'agrégat quotidien, pas un COUNT(*) par jour sur
        // site_visits qui peut devenir volumineuse.
        $compteurs = VisitCounter::query()
            ->where('date', '>=', today()->subDays(29))
            ->get()
            ->keyBy(fn (VisitCounter $c) => $c->date->format('Y-m-d'));

        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d/m');
            $data[] = $compteurs->get($date->format('Y-m-d'))?->today_visits ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Visites',
                    'data' => $data,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
