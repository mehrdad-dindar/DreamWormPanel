<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Models\Transaction;
use Exception;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class MonthlyIncomeChart extends ChartWidget
{
    protected ?string $heading = 'نمودار درآمد ماه جاری';
    protected string $color = 'success';
    protected ?int $total = null;
    /**
     * @throws Exception
     */
    public function boot(): void
    {
        $this->total = Transaction::query()
            ->where('type', true)
            ->where('created_at', '>=', verta()->startMonth()->toCarbon())
            ->selectRaw('SUM(amount) as total')
            ->pluck('total')->first();
    }

    public function getDescription(): ?string
    {
        return  'مجموع درآمد ناخالص ' .verta()->format('F'). ' ماه : '.number_format($this->total).' تومان';
    }

    /**
     * @throws Exception
     */
    protected function getData(): array
    {
        $data = Cache::remember('monthly-income-chart-data', 60, function () {
            return Transaction::query()
                ->where('type', true)
                ->where('created_at', '>=', verta()->startMonth()->toCarbon())
                ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
                ->groupBy(['day'])
                ->orderBy('day')
                ->pluck('total', 'day')
                ->toArray();
        });

        $start = vertaToCarbon("subDays",30,ttl: (60 * 60 * 24));
        $end = vertaToCarbon("now",ttl: (60 * 60 * 24));

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
