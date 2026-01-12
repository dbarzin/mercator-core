<?php

namespace Mercator\Core\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mercator\Core\Models\User;

/**
 * Service de vérification des licences Mercator Enterprise
 *
 * Ce service vérifie la validité de la licence et contrôle l'accès
 * aux fonctionnalités Enterprise (comme le module BPMN).
 */
class LicenseService
{
    /**
     * Chemin du fichier de licence
     */
    protected string $licensePath;

    /**
     * Clé publique RSA pour vérifier les signatures
     */
    protected string $publicKey;

    /**
     * URL du serveur de licences Mercator-License
     */
    protected ?string $licenseServer;

    /**
     * Grace period en jours (période de grâce après expiration)
     */
    protected int $gracePeriodDays = 30;

    /**
     * Durée du cache de validation (en secondes)
     */
    protected int $cacheDuration = 86400; // 24 heures

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->licensePath = storage_path('app/license/license.json');
        $this->publicKey = config('license.public_key');
        $this->licenseServer = config('license.server_url');
        $this->gracePeriodDays = config('license.grace_period_days', 30);
    }

    /**
     * Vérifier si une licence valide existe
     *
     * @return bool
     */
    public function hasValidLicense($force = false): bool
    {
        $cacheKey = 'mercator_license_validated';
        if (!$force && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $license = $this->getLicense();
            $res = $this->isLicenseValid($license);

            Cache::put($cacheKey, $res, $this->cacheDuration);

            return $res;

        } catch (Exception $e) {
            // Log::warning('License check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les informations de la licence
     *
     * @return array|null
     * @throws Exception
     */
    public function getLicense(): ?array
    {
        // Vérifier le cache d'abord
        $cached = Cache::get('mercator_license');
        if ($cached !== null) {
            return $cached;
        }

        // Lire le fichier de licence
        if (!file_exists($this->licensePath)) {
            throw new Exception('License file not found');
        }

        $content = file_get_contents($this->licensePath);
        if ($content === false) {
            throw new Exception('Unable to read license file');
        }

        $license = json_decode($content, true);
        if ($license === null) {
            throw new Exception('Invalid license file format');
        }

        // Valider la structure
        $this->validateLicenseStructure($license);

        // Mettre en cache
        Cache::put('mercator_license', $license, $this->cacheDuration);

        return $license;
    }

    /**
     * Vérifier si la licence est valide
     *
     * @param array $license
     * @return bool
     */
    public function isLicenseValid(array $license): bool
    {
        // 1. Vérifier la signature
        if (!$this->verifySignature($license)) {
            Log::error('License signature verification failed');
            return false;
        }

        // 2. Vérifier l'expiration
        if (!$this->checkExpiration($license)) {
            Log::warning('License has expired');
            return false;
        }

        /*
        // 3. Validation locale réussie = accès garanti
        // La validation serveur est optionnelle et non-bloquante
        if ($this->licenseServer && !$this->shouldSkipServerValidation()) {
            // Lance la validation serveur en arrière-plan (non-bloquante)
            $this->validateWithServerAsync($license);
        }
        */
        return true;
    }

    /**
     * Vérifier la signature RSA de la licence
     *
     * @param array $license
     * @return bool
     */
    protected function verifySignature(array $license): bool
    {
        if (!isset($license['signature'])) {
            return false;
        }

        $signature = $license['signature'];

        // Préparer les données à vérifier (sans la signature)
        $data = $license;
        unset($data['signature']);
        $licenseData = json_encode($data, JSON_UNESCAPED_SLASHES);

        // Charger la clé publique
        $publicKey = openssl_pkey_get_public($this->publicKey);
        if (!$publicKey) {
            Log::error('Unable to load public key');
            return false;
        }

        // Vérifier la signature
        $result = openssl_verify(
            $licenseData,
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        openssl_free_key($publicKey);

        return $result === 1;
    }

    /**
     * Vérifier l'expiration de la licence
     *
     * @param array $license
     * @return bool
     */
    protected function checkExpiration(array $license): bool
    {
        // Pas de date d'expiration = licence perpétuelle
        if (!isset($license['expiration_date'])) {
            return true;
        }

        $expirationDate = new \DateTime($license['expiration_date']);
        $now = new \DateTime();

        // Vérifier si expiré
        if ($now > $expirationDate) {
            // Vérifier la grace period
            $gracePeriod = new \DateInterval("P{$this->gracePeriodDays}D");
            $graceEnd = (clone $expirationDate)->add($gracePeriod);

            if ($now > $graceEnd) {
                return false;
            }

            Log::warning('License expired but within grace period', [
                'expiration_date' => $license['expiration_date'],
                'grace_period_ends' => $graceEnd->format('Y-m-d'),
            ]);
        }

        return true;
    }

    /**
     * Valider la licence auprès du serveur Mercator-License
     *
     * @return bool
     */
    public function validateWithServer(): bool
    {
        try {
            $license = $this->getLicense();

            $response = Http::timeout(10)
                ->post("{$this->licenseServer}/api/v1/licenses/validate", [
                    'license_key' => $license['license_key'],
                    'telemetry' => $this->collectTelemetry(),
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $isValid = $result['valid'] ?? false;

                if (!$isValid && isset($result['error'])) {
                    Log::error('License validation failed on server', [
                        'error' => $result['error'],
                    ]);
                }

                return $isValid;
            }

            Log::warning('Unable to validate license with server', [
                'status' => $response->status(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::warning('License server validation failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Vérifier si un module spécifique est disponible dans la licence
     *
     * @param string $module
     * @return bool
     */
    public function hasModule(string $module): bool
    {
        try {
            $license = $this->getLicense();

            if (!$this->isLicenseValid($license)) {
                return false;
            }

            $modules = $license['modules'] ?? [];
            return in_array($module, $modules);
        } catch (Exception $e) {
            Log::warning("Module check failed for '{$module}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si le client a accès à un module
     * (alias de hasModule pour plus de clarté dans le code)
     *
     * @param string $module
     * @return bool
     */
    public function hasModuleAccess(string $module): bool
    {
        return $this->hasModule($module);
    }

    /**
     * Obtenir tous les modules disponibles dans la licence
     *
     * @return array
     */
    public function getModules(): array
    {
        try {
            $license = $this->getLicense();
            return $license['modules'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtenir le nombre maximum d'utilisateurs
     *
     * @return int
     */
    public function getMaxUsers(): int
    {
        try {
            $license = $this->getLicense();
            return $license['max_users'] ?? 10;
        } catch (Exception $e) {
            return 10; // Valeur par défaut
        }
    }

    /**
     * Obtenir le type de licence
     *
     * @return string
     */
    public function getLicenseType(): string
    {
        try {
            $license = $this->getLicense();
            return $license['type'] ?? 'community';
        } catch (Exception $e) {
            return 'community';
        }
    }

    /**
     * Vérifier si la licence va bientôt expirer
     *
     * @param int $daysThreshold
     * @return bool
     */
    public function isExpiringSoon(int $daysThreshold = 30): bool
    {
        try {
            $license = $this->getLicense();

            if (!isset($license['expiration_date'])) {
                return false; // Licence perpétuelle
            }

            $expirationDate = new \DateTime($license['expiration_date']);
            $threshold = new \DateTime("+{$daysThreshold} days");

            return $expirationDate <= $threshold;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir les informations de licence pour l'affichage
     *
     * @return array
     */
    public function getLicenseInfo(): array
    {
        try {
            $license = $this->getLicense();
            $isValid = $this->isLicenseValid($license);

            $info = [
                'valid' => $isValid,
                'type' => $license['type'] ?? 'unknown',
                'issued_to' => $license['issued_to'] ?? 'Unknown',
                'license_key' => $this->maskLicenseKey($license['license_key'] ?? ''),
                'issued_date' => $license['issued_date'] ?? null,
                'expiration_date' => $license['expiration_date'] ?? null,
                'modules' => $license['modules'] ?? [],
                'max_users' => $license['max_users'] ?? 10,
            ];

            // Ajouter des informations d'expiration
            if (isset($license['expiration_date'])) {
                $expirationDate = new \DateTime($license['expiration_date']);
                $now = new \DateTime();
                $diff = $now->diff($expirationDate);

                if ($expirationDate < $now) {
                    $info['status'] = 'expired';
                    $info['days_until_expiration'] = -$diff->days;
                } else {
                    $info['status'] = $this->isExpiringSoon() ? 'expiring_soon' : 'active';
                    $info['days_until_expiration'] = $diff->days;
                }
            } else {
                $info['status'] = 'perpetual';
                $info['days_until_expiration'] = null;
            }

            return $info;
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Masquer partiellement la clé de licence
     *
     * @param string $key
     * @return string
     */
    protected function maskLicenseKey(string $key): string
    {
        if (strlen($key) <= 20) {
            return $key;
        }

        return substr($key, 0, 15) . '...' . substr($key, -4);
    }

    /**
     * Valider la structure de la licence
     *
     * @param array $license
     * @throws Exception
     */
    protected function validateLicenseStructure(array $license): void
    {
        $requiredFields = ['license_key', 'type', 'issued_to', 'issued_date', 'signature'];

        foreach ($requiredFields as $field) {
            if (!isset($license[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }

    /**
     * Déterminer si on doit sauter la validation serveur
     *
     * @return bool
     */
    protected function shouldSkipServerValidation(): bool
    {
        // Mode hors ligne activé
        if (config('license.offline_mode')) {
            return true;
        }

        // Dernière validation récente
        return Cache::has('mercator_license_server_validated');
    }

    /**
     * Collecter la télémétrie (si activée)
     *
     * @return array
     */
    protected function collectTelemetry(): array
    {
        if (!config('license.telemetry_enabled', false)) {
            return [];
        }

        return [
            'version' => config('app.version'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'users_count' => User::query()->count(),
        ];
    }

    /**
     * Forcer la revalidation de la licence
     *
     * @return bool
     */
    public function revalidate(): bool
    {
        Cache::forget('mercator_license');
        Cache::forget('mercator_license_server_validated');

        return $this->hasValidLicense();
    }

    /**
     * Invalider le cache de la licence
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('mercator_license');
        Cache::forget('mercator_license_server_validated');
    }
}
