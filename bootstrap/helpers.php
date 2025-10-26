<?php


function verifySignature(string $payload, ?string $signatureHeader, ?string $secret): bool
{
    if (! $signatureHeader || ! $secret) return false;
    $hash = base64_encode(hash_hmac('sha256', $payload, $secret, true));
    return hash_equals($hash, $signatureHeader);
}
