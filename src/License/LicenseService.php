<?php

namespace Mercator\Core\License;

use Carbon\Carbon;

class LicenseService
{
    /**
     * Modules autorisés offline
     */
    public function hasModuleAccess(string $module): bool
    {
        $token = config('app.license');
        if ($token==null) {
            logger()->info("License: No licence key found !");
            return false;
        }

        $data = $this->verifyLicenseKey($token);

        if ($data==null) {
            logger()->error("License: Invalid license key found !");
            return false;
        }

        if (!$this->isTimeValid($data)) {
            return false;
        }

        $modules = $data['modules'] ?? null;

        if ($modules === null) {
            return true; // tous modules autorisés
        }

        return in_array($module, $modules, true);
    }

    /**
     * Vérifie la validité temporalité (offline)
     */
    protected function isTimeValid(array $data): bool
    {
        return now()->lessThanOrEqualTo($data['grace_until']);
    }

    /**
     * Vérifie un token de licence (string).
     */
    public function verifyLicenseKey(string $licenseKey): ?array
    {
        if (! extension_loaded('sodium')) {
            throw new \RuntimeException('License: The sodium extension is required for Ed25519 signatures.');
        }

        $now = \Illuminate\Support\Carbon::now();

        $parts = explode('.', $licenseKey);
        if (count($parts) !== 2) {
            logger()->error("License: Invalid license format !");
            return null;
        }

        [$payloadB64, $sigB64] = $parts;

        $payloadJson = $this->base64UrlDecode($payloadB64);
        if ($payloadJson === null) {
            logger()->error('License: invalid payload encoding');
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload)) {
            logger()->error('License: invalid payload json');
            return null;
        }

        $signature = $this->base64UrlDecode($sigB64);
        if ($signature === null) {
            logger()->error('License: invalid signature encoding');
            return null;
        }

        // $publicKeyB64 = config('app.public_key');
        $publicKeyB64 = "+xsldp2d2oQec2nOZmEOEo6/jmKsVtzlQ51p+e7yWBE=";
        /*
        if (empty($publicKeyB64)) {
            logger()->error('license: missing public key');
            return null;
        }
        */

        $publicKey = base64_decode($publicKeyB64, true);
        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            logger()->error('license invalid_public_key_encoding');
            return null;
        }

        $verified = sodium_crypto_sign_verify_detached($signature, $payloadB64, $publicKey);
        if (! $verified) {
            logger()->error('license: invalid_signature');
            return null;
        }

        if (empty($payload['exp'])) {
            logger()->error('license: missing expiration');
            return null;
        }

        $exp = Carbon::parse($payload['exp']);
        $graceDays  = (int) ($payload['grace_days'] ?? 0);
        $graceUntil = $exp->copy()->addDays($graceDays);

        if ($now->lessThanOrEqualTo($exp)) {
            $status = 'valid';
            $valid  = true;
        } elseif ($now->lessThanOrEqualTo($graceUntil)) {
            $status = 'grace';
            $valid  = true;
        } else {
            $status = 'expired';
            $valid  = false;
        }

        return [
            'valid'       => $valid,
            'status'      => $status,
            'expires_at'  => $exp,
            'grace_until' => $graceUntil,
            'payload'     => $payload,
        ];
    }

    protected function invalid(string $status, ?array $payload = null): array
    {
        return [
            'valid'       => false,
            'status'      => $status,
            'expires_at'  => null,
            'grace_until' => null,
            'payload'     => $payload,
        ];
    }

    protected function base64UrlDecode(string $data): ?string
    {
        $data = strtr($data, '-_', '+/');
        $padding = 4 - (strlen($data) % 4);
        if ($padding < 4) {
            $data .= str_repeat('=', $padding);
        }

        $decoded = base64_decode($data, true);

        return $decoded === false ? null : $decoded;
    }




}
