<?php

namespace App\Filament\Resources\WorkSessionResource\Widgets;

use App\Models\WorkSession;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class WorkSessionChart extends ChartWidget
{
    protected static ?string $heading = 'نمودار ساعت‌های کارکرد ماه جاری';
    protected static ?string $description = null;

    protected function getData(): array
    {
        $userId = Auth::id();

        $data = WorkSession::where('user_id', $userId)
            ->whereMonth('start_time', verta()->startMonth()->toCarbon())
            ->orWhereMonth('start_time', verta()->endMonth()->toCarbon())
            ->whereYear('start_time', verta()->startYear()->toCarbon())
            ->orWhereYear('start_time', verta()->endYear()->toCarbon())
            ->whereNotNull('end_time')
            ->get()
            ->groupBy(function ($session) {
                return Carbon::parse($session->start_time)->toDateString();
            })
            ->map(function ($sessions) {
                return intval($sessions->sum(function ($session) {
                        return Carbon::parse($session->start_time)->diffInMinutes(Carbon::parse($session->end_time));
                    }));
            })
            ->toArray();
        $total = array_sum($data);

        self::$description = "مجموع کارکرد ماه شما: ". $this->getTotalToClock($total);

        // تولید روزهای کامل ماه
        $start = verta()->startMonth()->toCarbon();
        $end = verta()->toCarbon();
        $days = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $day = $date->toDateString();
            $days[$day] = $data[$day] ?? 0;
        }
        $labels = array_map(function ($item) {
            return verta($item)->format('d F');
        }, array_keys($days));

        return [
            'datasets' => [
                [
                    'label' => 'کارکرد روز (دقیقه)',
                    'data' => array_values($days),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getTotalToClock(int $total)
    {
        $hour = intval($total / 60);
        $minutes = $total % 60;
        return $hour.' ساعت و '.$minutes.' دقیقه';
    }

}
