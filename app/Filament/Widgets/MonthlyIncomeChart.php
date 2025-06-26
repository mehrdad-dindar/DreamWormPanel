<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Exception;
use Filament\Widgets\ChartWidget;

class MonthlyIncomeChart extends ChartWidget
{
    protected static ?string $heading = 'نمودار درآمد ماه جاری';
    protected static string $color = 'success';
    protected static ?string $description = null;

    /**
     * @throws Exception
     */
    public function boot(): void
    {
        $total = Transaction::query()
            ->where('type', true)
            ->where('created_at', '>=', verta()->startMonth()->toCarbon())
            ->selectRaw('SUM(amount) as total')
            ->pluck('total')->first();
        self::$description = 'مجموع درآمد ناخالص ' .verta()->format('F'). ' ماه : '.number_format($total).' تومان';
    }

    /**
     * @throws Exception
     */
    protected function getData(): array
    {
        $data = Transaction::query()
            ->where('type', true)
            ->where('created_at', '>=', verta()->startMonth()->toCarbon())
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $start = verta()->startMonth()->toCarbon();
        $end = verta()->now()->toCarbon();

        $days = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $days[$date->toDateString()] = 0;
        }

        foreach ($data as $day => $total) {
            $days[$day] = $total;
        }
        $labels = array_map(function ($item) {
            return verta($item)->format('d F');
        }, array_keys($days));

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'دریافتی',
                    'data' => array_values($days),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
