<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $guarded;
    protected $casts = [
        'watering_dates' => 'array',
        'feeding_dates' => 'array',
        'fertilization_dates' => 'array',
    ];

    public static function calculateDates($type = null, $start_at = null, $period = 6, $interval = 3): array
    {
        $addWeeks = match ($type){
            'water' => 2,
            'feed' => 4,
            'fertilize' => 6,
            default => 0
        };
        $dates = [];
        $start_at = $start_at ?? now();
        for ($i = 0; $i < $period; $i++) {
            $dates[] = ['date' => Carbon::parse($start_at)->addWeeks($addWeeks)->addDays($i * $interval)];
        }
        return $dates;
    }
}
