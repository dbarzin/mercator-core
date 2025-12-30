<?php
// OLD CODE
namespace Mercator\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Mercator\Core\License\LicenseService;
use Mercator\Core\Menus\MenuRegistry;
use Mercator\Core\Modules\ModuleRegistry;
use Mercator\Core\Permissions\PermissionRegistry;

class MercatorCoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Charger la config du package et la merger dans config('mercator')
        $this->mergeConfig();

        // MenuRegistry : registre central des menus Mercator
        $this->app->singleton(MenuRegistry::class, function ($app) {
            return new MenuRegistry();
        });
        $this->app->alias(MenuRegistry::class, 'mercator.menus');

        // ModuleRegistry : état des modules (installés / activés)
        $this->app->singleton(ModuleRegistry::class, function ($app) {
            return new ModuleRegistry();
        });
        $this->app->alias(ModuleRegistry::class, 'mercator.modules');

        // PermissionRegistry : registre central des permissions Mercator
        $this->app->singleton(PermissionRegistry::class, function ($app) {
            return new PermissionRegistry();
        });
        $this->app->alias(PermissionRegistry::class, 'mercator.permissions');

        // LicenseManager : gestion et validation de la licence Mercator
        $this->app->singleton(LicenseService::class, function ($app) {
            return new LicenseService();
        });
        $this->app->alias(LicenseService::class, 'mercator.license');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishConfig();
    }

    /**
     * Merge la config du package dans config('mercator').
     */
    protected function mergeConfig(): void
    {
        $configPath = __DIR__ . '/../config/mercator.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'mercator');
        }
    }

    /**
     * Permet de publier la config du package vers l'app hôte.
     */
    protected function publishConfig(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $configPath = __DIR__ . '/../config/mercator.php';

        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('mercator.php'),
            ], 'mercator-config');
        }
    }
}
