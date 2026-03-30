<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingReportsStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Rapports en attente', Report::where('status', 'submitted')->count())
                ->description('Nécessitent votre validation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Rapports validés (Semaine)', Report::where('status', 'validated')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count())
                ->description('Cette semaine')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Rapports (Mois)', Report::whereMonth('created_at', now()->month)->count())
                ->description('Ce mois-ci')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}