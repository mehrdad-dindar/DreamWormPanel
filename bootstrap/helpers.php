<?php


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
