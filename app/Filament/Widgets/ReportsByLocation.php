<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Widgets\ChartWidget;

class ReportsByLocation extends ChartWidget
{
    protected static ?string $heading = 'Rapports par Lieu';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Report::selectRaw('location_name, count(*) as count')
            ->groupBy('location_name')
            ->pluck('count', 'location_name');

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de rapports',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#36A2EB',
                        '#FF6384',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                    ],
                ],
            ],
            'labels' => $data->keys()->map(fn ($key) => ucfirst(str_replace('_', ' ', $key)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // ou 'bar', 'line', 'doughnut'
    }
}