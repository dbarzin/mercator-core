<?php

namespace Mercator\Core\License;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class LicenseToken
{
    public function decode(string $token): ?array
    {
        [$payload, $sig] = explode('.', $token, 2) + [null, null];

        if (!$payload || !$sig) {
            return null;
        }

        $decoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        return $decoded ?? null;
    }

    public function verify(string $token): bool
    {
        [$payload, $sig] = explode('.', $token, 2) + [null, null];

        if (!$payload || !$sig) {
            return false;
        }

        $expected = $this->sign($payload);

        return hash_equals($expected, $sig);
    }

    protected function sign(string $payload): string
    {
        // Même algo qu’au backend, secret partagé via .env ou config
        $secret = config('mercator.license_secret');

        return rtrim(strtr(
            base64_encode(hash_hmac('sha256', $payload, $secret, true)),
            '+/', '-_'
        ), '=');
    }

    /**
     * Vérifie la validité temporalité (offline)
     */
    public function isTimeValid(array $data): bool
    {
        $exp = Carbon::parse($data['exp']);
        $grace = $data['grace_days'] ?? 0;

        return now()->lessThanOrEqualTo($exp->clone()->addDays($grace));
    }

}
