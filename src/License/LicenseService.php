<?php

namespace Mercator\Core\License;

use Mercator\Core\Models\LocalLicense;

class LicenseService
{
    public function __construct(
        protected LicenseToken $tokenService
    ) {}

    /**
     * Vérification offline (clé principale)
     */
    public function isOfflineValid(): bool
    {
        $local = LocalLicense::query()->first();

        if (!$local) {
            return false;
        }

        $token = $local->license_token;

        if (!$this->tokenService->verify($token)) {
            return false;
        }

        $data = $this->tokenService->decode($token);

        if (!$data) {
            return false;
        }

        return $this->tokenService->isTimeValid($data);
    }

    /**
     * Modules autorisés offline
     */
    public function hasModuleAccess(string $module): bool
    {
        try {
            $local = LocalLicense::query()->first();
            if (!$local) {
                return false;
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return false;
        }

        $data = $this->tokenService->decode($local->license_token);

        $modules = $data['modules'] ?? null;

        if ($modules === null) {
            return true; // tous modules autorisés
        }

        return in_array($module, $modules, true);
    }

    /**
     * Contact non bloquant avec le serveur de licence
     */
    public function checkOnline(): void
    {
        $local = LocalLicense::query()->first();
        if (!$local) {
            return;
        }

        try {
            $response = Http::timeout(3)
                ->get('https://license.sourcentis.com/api/license/check', [
                    'token' => $local->license_token,
                ])
                ->throw()
                ->json();

            $local->last_check_at = now();
            $local->last_check_status = $response['valid'] ? 'ok' : 'expired';
            $local->last_check_error = null;
            $local->save();
        } catch (\Throwable $e) {
            $local->last_check_at = now();
            $local->last_check_status = 'error';
            $local->last_check_error = $e->getMessage();
            $local->save();
        }
    }
}
