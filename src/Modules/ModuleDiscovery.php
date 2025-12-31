<?php

namespace Mercator\Core\Modules;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Découverte automatique des modules Mercator via Composer
 *
 * Scanne le fichier vendor/composer/installed.json pour trouver
 * tous les packages qui déclarent un module Mercator dans leur extra.mercator-module
 */
class ModuleDiscovery
{
    /**
     * Chemin vers le fichier installed.json de Composer
     */
    protected string $installedPath;

    /**
     * Cache en mémoire des modules découverts
     */
    protected ?array $cache = null;

    /**
     * Clé de cache Laravel
     */
    protected const CACHE_KEY = 'mercator_discovered_modules';

    /**
     * Durée du cache (en secondes) - 1 heure
     */
    protected const CACHE_DURATION = 3600;

    /**
     * Constructor
     */
    public function __construct(?string $installedPath = null)
    {
        $this->installedPath = $installedPath
            ?? base_path('vendor/composer/installed.json');
    }

    /**
     * Retourne la liste de tous les modules déclarés dans les packages Composer.
     *
     * Format retourné:
     * [
     *   'bpmn' => [
     *     'package' => 'mercator/mercator-bpmn',
     *     'name' => 'bpmn',
     *     'label' => 'BPMN Editor',
     *     'description' => 'Advanced BPMN 2.0 process modeling',
     *     'version' => '1.0.0',
     *     'provider' => 'Mercator\BPMN\BPMNServiceProvider',
     *     'routes' => ['bpmn.*', 'api.bpmn.*'],
     *     'permissions' => ['bpmn_view', 'bpmn_manage'],
     *   ],
     *   ...
     * ]
     *
     * @return array<string, array>
     */
    public function discover(): array
    {
        \Log::debug('Discovering modules');

        // 1. Vérifier le cache mémoire
        if ($this->cache !== null) {
            return $this->cache;
        }

        // 2. Vérifier le cache Laravel
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $this->cache = $cached;
        }

        // 3. Découvrir les modules
        $modules = $this->discoverFromComposer();

        // 4. Mettre en cache
        Cache::put(self::CACHE_KEY, $modules, self::CACHE_DURATION);
        $this->cache = $modules;

        return $modules;
    }

    /**
     * Découvrir les modules depuis le fichier installed.json
     *
     * @return array<string, array>
     */
    protected function discoverFromComposer(): array
    {
        \Log::debug('Discovering modules from Composer installed.json');

        if (!file_exists($this->installedPath)) {
            Log::warning("Composer installed.json not found at: {$this->installedPath}");
            return [];
        }

        $content = @file_get_contents($this->installedPath);
        if ($content === false) {
            Log::error("Unable to read Composer installed.json");
            return [];
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            Log::error("Invalid JSON in Composer installed.json");
            return [];
        }

        // Composer 2.x utilise "packages", Composer 1.x est un array direct
        $packages = $decoded['packages'] ?? $decoded;

        if (!is_array($packages)) {
            return [];
        }

        $modules = [];

        foreach ($packages as $pkg) {
            if (!is_array($pkg)) {
                continue;
            }

            $extra = Arr::get($pkg, 'extra', []);

            // Vérifier si le package déclare un module Mercator
            if (!isset($extra['mercator-module']) || !is_array($extra['mercator-module'])) {
                continue;
            }

            $meta = $extra['mercator-module'];

            // Le nom du module est obligatoire
            $name = $meta['name'] ?? null;
            if (!$name || !is_string($name)) {
                Log::warning("Module in package {$pkg['name']} has no name", ['package' => $pkg]);
                continue;
            }

            // Construire les métadonnées du module
            $modules[$name] = [
                'package'     => $pkg['name'] ?? 'unknown',
                'name'        => $name,
                'label'       => $meta['label'] ?? $name,
                'description' => $meta['description'] ?? '',
                'version'     => $meta['version'] ?? ($pkg['version'] ?? '0.0.0'),
                'provider'    => $meta['provider'] ?? null,
                'routes'      => $meta['routes'] ?? [],
                'permissions' => $meta['permissions'] ?? [],
                'icon'        => $meta['icon'] ?? 'bi bi-puzzle',
                'order'       => $meta['order'] ?? 100,
            ];
        }

        return $modules;
    }

    /**
     * Retourne les métadonnées d'un module donné (ou null)
     *
     * @param string $name Nom du module
     * @return array|null
     */
    public function getMeta(string $name): ?array
    {
        $modules = $this->discover();

        return $modules[$name] ?? null;
    }

    /**
     * Trouve un module par son nom ou son package Composer
     *
     * @param string $arg Nom du module ou nom du package
     * @return array|null
     */
    public function findByNameOrPackage(string $arg): ?array
    {
        $modules = $this->discover();

        foreach ($modules as $meta) {
            // Correspondance par nom (insensible à la casse)
            if (strcasecmp($meta['name'], $arg) === 0) {
                return $meta;
            }

            // Correspondance par package (insensible à la casse)
            if (strcasecmp($meta['package'], $arg) === 0) {
                return $meta;
            }
        }

        return null;
    }

    /**
     * Vérifier si un module existe
     *
     * @param string $name Nom du module
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->getMeta($name) !== null;
    }

    /**
     * Obtenir tous les noms de modules découverts
     *
     * @return array<string>
     */
    public function getModuleNames(): array
    {
        return array_keys($this->discover());
    }

    /**
     * Obtenir le nombre de modules découverts
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->discover());
    }

    /**
     * Invalider le cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Forcer la redécouverte des modules
     *
     * @return array<string, array>
     */
    public function rediscover(): array
    {
        $this->clearCache();
        return $this->discover();
    }
}