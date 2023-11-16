<?php

namespace App\Filament\Widgets;

use App\Enums\RentStatusEnum;
use App\Models\Rent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RentChart extends ChartWidget
{
    protected static ?string $heading = '';

    protected function getData(): array
    {
        $data = Rent::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

        return [
            
            'datasets' => [
                [
                    'label' => 'rents',
                    'data' => array_values($data)
                ]
            ],
            'labels' => RentStatusEnum::cases()

        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
