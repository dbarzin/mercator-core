<?php
// Reviewed code
namespace Mercator\Core\Modules;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Registry pour gérer les modules Mercator Enterprise
 *
 * Combine la découverte automatique (ModuleDiscovery) avec l'état
 * persisté en base de données (enabled/disabled).
 */
class ModuleRegistry
{
    /**
     * Service de découverte des modules
     */
    protected ModuleDiscovery $discovery;

    /**
     * Cache des modules en base de données
     *
     * @var array<string, object>
     */
    protected array $modules = [];

    /**
     * Nom de la table
     */
    protected const TABLE = 'mercator_modules';

    /**
     * Clé de cache pour les modules
     */
    protected const CACHE_KEY = 'mercator_modules_registry';

    /**
     * Durée du cache (en secondes)
     */
    protected const CACHE_DURATION = 3600;

    /**
     * Constructor
     */
    public function __construct(ModuleDiscovery $discovery)
    {
        $this->discovery = $discovery;
        $this->reload();
    }

    /**
     * Recharger les modules depuis la base de données
     *
     * @return void
     */
    protected function reload(): void
    {
        // Vérifier le cache d'abord
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null && is_array($cached)) {
            $this->modules = $cached;
            return;
        }

        // Vérifier que la table existe
        if (!$this->databaseReady()) {
            $this->modules = [];
            return;
        }

        try {
            $this->modules = DB::table(self::TABLE)
                ->get()
                ->keyBy('name')
                ->all();

            // Mettre en cache
            Cache::put(self::CACHE_KEY, $this->modules, self::CACHE_DURATION);
        } catch (\Exception $e) {
            Log::error('Failed to reload modules from database: ' . $e->getMessage());
            $this->modules = [];
        }
    }

    /**
     * Retourne tous les modules connus en base de données
     *
     * @return array<string, object>
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Retourne tous les modules avec leurs métadonnées complètes
     * (découverte Composer + état en base)
     *
     * Format:
     * [
     *   'bpmn' => [
     *     'meta' => [...],      // Métadonnées depuis Composer
     *     'installed' => true,  // En base de données
     *     'enabled' => true,    // Activé
     *     'version' => '1.0.0', // Version en base
     *   ],
     *   ...
     * ]
     *
     * @return array
     */
    public function getAllWithMeta(): array
    {
        $discovered = $this->discovery->discover();
        $result = [];

        foreach ($discovered as $name => $meta) {
            $dbModule = $this->modules[$name] ?? null;

            $result[$name] = [
                'meta'       => $meta,
                'installed'  => $dbModule !== null,
                'enabled'    => $dbModule ? (bool) $dbModule->enabled : false,
                'version'    => $dbModule->version ?? $meta['version'] ?? '0.0.0',
            ];
        }

        return $result;
    }

    /**
     * Vérifier si un module est activé
     *
     * @param string $name Nom du module
     * @return bool
     */
    public function isEnabled(string $name): bool
    {
        return isset($this->modules[$name]) && (bool) $this->modules[$name]->enabled;
    }

    /**
     * Vérifier si un module est installé (en base de données)
     *
     * @param string $name Nom du module
     * @return bool
     */
    public function isInstalled(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Vérifier si un module existe (découvert par Composer)
     *
     * @param string $name Nom du module
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->discovery->exists($name);
    }

    /**
     * Installer un module (l'ajouter à la base de données)
     *
     * @param string $name Nom du module
     * @param array|null $meta Métadonnées optionnelles (sinon découvertes automatiquement)
     * @return void
     */
    public function install(string $name, ?array $meta = null): void
    {
        if (!$this->databaseReady()) {
            Log::warning("Cannot install module '{$name}': database not ready");
            return;
        }

        // Récupérer les métadonnées si non fournies
        if ($meta === null) {
            $meta = $this->discovery->getMeta($name);
            if ($meta === null) {
                Log::error("Cannot install module '{$name}': not found in Composer packages");
                return;
            }
        }

        try {
            DB::table(self::TABLE)->updateOrInsert(
                ['name' => $name],
                [
                    'label'      => $meta['label'] ?? $name,
                    'version'    => $meta['version'] ?? '0.0.0',
                    'enabled'    => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            Log::info("Module installed: {$name}");
            $this->clearCache();
            $this->reload();
        } catch (\Exception $e) {
            Log::error("Failed to install module '{$name}': " . $e->getMessage());
        }
    }

    /**
     * Activer un module
     *
     * @param string $name Nom du module
     * @return bool Succès ou échec
     */
    public function enable(string $name): bool
    {
        if (!$this->databaseReady()) {
            return false;
        }

        // Vérifier que le module existe dans Composer
        if (!$this->exists($name)) {
            Log::warning("Cannot enable module '{$name}': not found in Composer packages");
            return false;
        }

        // Installer le module s'il n'est pas en base
        if (!$this->isInstalled($name)) {
            $this->install($name);
            return true; // install() active par défaut
        }

        try {
            DB::table(self::TABLE)
                ->where('name', $name)
                ->update([
                    'enabled'    => true,
                    'updated_at' => now(),
                ]);

            Log::info("Module enabled: {$name}");
            $this->clearCache();
            $this->reload();

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to enable module '{$name}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Désactiver un module
     *
     * @param string $name Nom du module
     * @return bool Succès ou échec
     */
    public function disable(string $name): bool
    {
        if (!$this->databaseReady()) {
            return false;
        }

        if (!$this->isInstalled($name)) {
            return false; // Pas installé = déjà désactivé
        }

        try {
            DB::table(self::TABLE)
                ->where('name', $name)
                ->update([
                    'enabled'    => false,
                    'updated_at' => now(),
                ]);

            Log::info("Module disabled: {$name}");
            $this->clearCache();
            $this->reload();

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to disable module '{$name}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Désinstaller un module (le retirer de la base de données)
     *
     * @param string $name Nom du module
     * @return void
     */
    public function uninstall(string $name): void
    {
        if (!$this->databaseReady()) {
            return;
        }

        try {
            DB::table(self::TABLE)
                ->where('name', $name)
                ->delete();

            Log::info("Module uninstalled: {$name}");
            $this->clearCache();
            $this->reload();
        } catch (\Exception $e) {
            Log::error("Failed to uninstall module '{$name}': " . $e->getMessage());
        }
    }

    /**
     * Synchroniser un module avec les permissions
     *
     * @param string $module Nom du module
     * @return void
     */
    public function syncPermissions(string $module): void
    {
        if (!app()->bound('mercator.permissions')) {
            return;
        }

        try {
            $permissions = app('mercator.permissions')->forModule($module);

            foreach ($permissions as $permission) {
                DB::table('permissions')->updateOrInsert(
                    ['title' => $permission->title],
                    [
                        'module'     => $module,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            Log::info("Permissions synced for module: {$module}");
        } catch (\Exception $e) {
            Log::error("Failed to sync permissions for module '{$module}': " . $e->getMessage());
        }
    }

    /**
     * Découvrir et installer automatiquement tous les modules nouveaux
     *
     * @return array Liste des modules installés
     */
    public function autoDiscover(): array
    {
        $discovered = $this->discovery->discover();
        $installed = [];

        foreach ($discovered as $name => $meta) {
            if (!$this->isInstalled($name)) {
                $this->install($name, $meta);
                $installed[] = $name;
            }
        }

        return $installed;
    }

    /**
     * Obtenir le statut complet d'un module
     *
     * @param string $name Nom du module
     * @return array|null
     */
    public function getStatus(string $name): ?array
    {
        $meta = $this->discovery->getMeta($name);
        if ($meta === null) {
            return null;
        }

        $dbModule = $this->modules[$name] ?? null;

        return [
            'name'       => $name,
            'meta'       => $meta,
            'discovered' => true,
            'installed'  => $dbModule !== null,
            'enabled'    => $dbModule ? (bool) $dbModule->enabled : false,
            'version'    => $dbModule->version ?? $meta['version'] ?? '0.0.0',
        ];
    }

    /**
     * Vérifier si la base de données est prête
     *
     * @return bool
     */
    protected function databaseReady(): bool
    {
        try {
            return DB::connection()->getPdo() !== null
                && Schema::hasTable(self::TABLE);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Invalider le cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->discovery->clearCache();
    }
}