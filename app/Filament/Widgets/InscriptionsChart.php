<?php

namespace App\Filament\Widgets;

use App\Models\Inscription;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class InscriptionsChart extends ChartWidget
{
    // $heading doit être non statique
    protected ?string $heading = 'Évolution des inscriptions (30 derniers jours)';
    
    // $sort doit être statique
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d/m');
            
            $count = Inscription::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Nouvelles inscriptions',
                    'data' => $data,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
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