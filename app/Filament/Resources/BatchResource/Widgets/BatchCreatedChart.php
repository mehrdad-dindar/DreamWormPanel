<?php

namespace App\Filament\Resources\BatchResource\Widgets;

use App\Models\Batch;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BatchCreatedChart extends ChartWidget
{
    protected ?string $heading = 'نمودار ثبت دسته‌ها';

    protected function getData(): array
    {
        $batches = Batch::where('expected_harvest_date','>',Carbon::now()->addWeeks(2))->orderBy('batch_number')->get(['batch_number', 'actual_boxes']);

        $labels = $batches->pluck('batch_number')->map(fn($n) => "دسته $n")->toArray();
        $values = $batches->pluck('actual_boxes')->toArray();


        return [
            'datasets' => [
                [
                    'label' => 'تعداد جعبه‌ها '.$batches->sum('actual_boxes'),
                    'data' => $values,
                    'backgroundColor' => '#38bdf8',
                    'borderColor' => '#0284c7',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];

    }

    protected function getType(): string
    {
        return 'bar';
    }
}
