<?php

namespace App\Filament\Resources\WorkSessions\Widgets;

use App\Models\User;
use App\Models\WorkSession;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class WorkSessionChart extends ChartWidget
{

    protected ?string $heading = 'نمودار ساعت‌های کارکرد ماه جاری';
    protected ?string $total = null;

    public function getDescription(): string|Htmlable|null
    {
        return "مجموع کارکرد ماه کارگاه: ". $this->total;
    }

    protected function getData(): array
    {
        $start = verta()->startMonth()->toCarbon();
        $end = verta()->toCarbon();

        // دریافت همه سشن‌های مربوط به این ماه
        $sessions = WorkSession::whereBetween('start_time', [$start, $end])
            ->whereNotNull('end_time')
            ->get();

        $this->total = sumHours($sessions);

        // تاریخ‌های ماه جاری
        $days = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $days[$date->toDateString()] = verta($date)->format('d F');
        }

        // استخراج یکتای user_id ها
        $userIds = $sessions->pluck('user_id')->unique();

        // آماده‌سازی دیتاست‌ها برای هر کاربر
        $datasets = [];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $name = $user?->name ?? "کاربر {$userId}";
            $color = $this->getColorByUser($userId);

            $dailyData = [];
            $totalMinutes = 0;

            foreach ($days as $day) {
                $minutes = $sessions
                    ->filter(fn($s) => $s->user_id == $userId && verta($s->start_time)->format('d F') == $day)
                    ->sum(fn($s) => Carbon::parse($s->start_time)->diffInMinutes(Carbon::parse($s->end_time)));

                $dailyData[] = round($minutes / 60,1);
                $totalMinutes += $minutes;
            }

            // تبدیل به ساعت و دقیقه
            $hours = floor($totalMinutes / 60);
            $totalFormatted = $hours;

            $datasets[] = [
                'label' => "{$name} ({$totalFormatted})",
                'data' => $dailyData,
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        }

        return [
            'labels' => array_values($days),
            'datasets' => $datasets,
        ];
    }


    protected function getColorByUser($userId): string
    {
        $colors = [
            '#F44336', '#BFECFF', '#9C27B0', '#3F51B5',
            '#2196F3', '#FFF9BD', '#FF8080', '#009688',
            '#4CAF50', '#8BC34A', '#CDDC39', '#FFC107',
            '#FF9800', '#FF5722', '#795548', '#607D8B',
        ];
        return $colors[$userId % count($colors)];
    }


    protected function getType(): string
    {
        return 'bar';
    }

    private function getTotalToClock(int $total): string
    {
        dd($this->total);
        $hour = (int)($total / 60);
        $minutes = $total % 60;
        return $hour.' ساعت و '.$minutes.' دقیقه';
    }

}
