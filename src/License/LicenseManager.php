<?php

namespace Mercator\Core\License;

class LicenseManager
{
    private ?array $payload = null;
    private bool $valid = false;

    public function __construct()
    {
        $rawKey = config('app.licence'); // ou lecture DB
        if (!$rawKey) {
            return;
        }

        try {
            [$header, $payload, $signature] = $this->splitKey($rawKey);
            $this->verifySignature($header, $payload, $signature);
            $data = json_decode($payload, true);

            if ($this->isExpired($data)) {
                return;
            }

            if ($data['product'] !== 'mercator-enterprise') {
                return;
            }

            $this->payload = $data;
            $this->valid = true;
        } catch (\Throwable $e) {
            // log + invalidate
        }
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function features(): array
    {
        return $this->payload['features'] ?? [];
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features(), true);
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }
}
