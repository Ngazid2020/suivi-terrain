<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class RecentReports extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),

                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(40),

                TextColumn::make('user.name')
                    ->label('Agent'),

                TextColumn::make('location_name')
                    ->label('Lieu'),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'submitted' => 'warning',
                        'validated' => 'success',
                        'rejected' => 'danger',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->paginated(false);
    }
}