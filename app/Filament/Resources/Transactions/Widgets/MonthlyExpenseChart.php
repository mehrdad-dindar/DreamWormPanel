<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Models\Transaction;
use Exception;
use Filament\Widgets\ChartWidget;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use Illuminate\Support\Facades\Cache;

class MonthlyExpenseChart extends ChartWidget
{
    protected ?string $heading = 'نمودار هزینه ماه جاری';
    protected string $color = 'danger';
    protected ?int $total = null;

    /**
     * @throws Exception
     */
    public function boot(): void
    {
        $this->total = Transaction::query()
            ->where('type', false)
            ->where('created_at', '>=', verta()->startMonth()->toCarbon())
            ->selectRaw('SUM(amount) as total')
            ->pluck('total')->first();
    }

    public function getDescription(): ?string
    {
        return  'مجموع هزینه ناخالص ' .verta()->format('F'). ' ماه : '.number_format($this->total).' تومان';
    }

    /**
     * @throws Exception
     */
    protected function getData(): array
    {
        $data = Cache::remember('monthly-expense-chart-data', 60, function () {
            return Transaction::query()
                ->where('type', false)
                ->where('created_at', '>=', verta()->startMonth()->toCarbon())
                ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
                ->groupBy(['day'])
                ->orderBy('day')
                ->pluck('total', 'day')
                ->toArray();
        });

        $start = vertaToCarbon("subDays",30,ttl: (60 * 60 * 24));
        $end = vertaToCarbon("now",ttl: (60 * 60));

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
                    'label' => 'هزینه',
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
