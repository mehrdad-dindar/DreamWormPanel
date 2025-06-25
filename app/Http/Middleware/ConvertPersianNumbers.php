<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertPersianNumbers
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->convertNumbers($request->all()));
        return $next($request);
    }

    /**
     * Convert Persian and Arabic numbers in the given array to English.
     *
     * @param  array  $data
     * @return array
     */
    private function convertNumbers(array $data): array
    {

        return array_map(function ($item) {
            $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            if (is_array($item)) {
                return $this->convertNumbers($item);
            }
            if (is_string($item)) {
                $item = str_replace($persianDigits, $englishDigits, $item);
                $item = str_replace($arabicDigits, $englishDigits, $item);
            }
            return $item;
        }, $data);
    }
}
