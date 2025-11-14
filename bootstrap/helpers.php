<?php


use App\Models\WorkSession;
use Carbon\Carbon;

function verifySignature(string $payload, ?string $signatureHeader, ?string $secret): bool
{
    if (! $signatureHeader || ! $secret) return false;
    $hash = base64_encode(hash_hmac('sha256', $payload, $secret, true));
    return hash_equals($hash, $signatureHeader);
}

function vertaToCarbon($method, int $value = 2, $ttl = 3600)
{
    $cacheKey = "verta_to_carbon_{$method}{$value}";

    return Cache::remember($cacheKey, $ttl, function () use ($method,$value) {
        $verta = new Verta();

        switch ($method) {
            case 'startMonth':
                return $verta->startMonth()->toCarbon();
            case 'now':
                return $verta->toCarbon();
            case 'endMonth':
                return $verta->endMonth()->toCarbon();
            case 'startDay':
                return $verta->startDay()->toCarbon();
            case 'endDay':
                return $verta->endDay()->toCarbon();
            case 'subDays':
                return $verta->subDays($value)->toCarbon();
            default:
                throw new InvalidArgumentException("Method {$method} not supported in vertaToCarbon helper.");
        }
    });
}

if (!function_exists('formatMinutesToPersian')) {
    /**
     * تبدیل تعداد دقایق به رشتهٔ فارسی مانند "۳ ساعت و ۱۲ دقیقه"
     */
    function formatMinutesToPersian(int $totalMinutes): string
    {
        if ($totalMinutes <= 0) {
            return '0 دقیقه';
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        $output = '';
        if ($hours > 0) {
            $output .= "{$hours} ساعت";
        }
        if ($minutes > 0) {
            $output .= $hours > 0 ? " و {$minutes} دقیقه" : "{$minutes} دقیقه";
        }

        return $output;
    }
}

if (!function_exists('diffHours')) {
    function diffHours(WorkSession $workSession): string
    {
        if (!$workSession->start_time || !$workSession->end_time) {
            return '0 دقیقه';
        }

        $minutes = Carbon::parse($workSession->start_time)
            ->diffInMinutes($workSession->end_time); // ممکن است منفی باشد

        return formatMinutesToPersian(abs($minutes));
    }
}

if (!function_exists('sumHours')) {
    function sumHours($sessions): string
    {
        $totalMinutes = collect($sessions)
            ->filter(fn($item) => $item->start_time && $item->end_time)
            ->sum(fn($item) => Carbon::parse($item->start_time)->diffInMinutes($item->end_time));

        return formatMinutesToPersian(abs($totalMinutes));
    }
}
