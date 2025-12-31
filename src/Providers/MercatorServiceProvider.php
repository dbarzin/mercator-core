<?php
// New Code
namespace Mercator\Core\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Mercator\Core\Menus\MenuRegistry;
use Mercator\Core\Permissions\PermissionRegistry;
use Mercator\Core\Services\LicenseService;
use Mercator\Core\Modules\ModuleRegistry;
use Mercator\Core\Modules\ModuleDiscovery;

class MercatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Charger la config du package et la merger dans config('mercator')
        $this->mergeConfig();

        // Enregistrer le service de licence en singleton
        $this->app->singleton(LicenseService::class, function ($app) {
            return new LicenseService();
        });

        // Enregistrer le service de découverte des modules en singleton
        $this->app->singleton(ModuleDiscovery::class, function ($app) {
            return new ModuleDiscovery();
        });

        // Enregistrer le registry des modules en singleton
        $this->app->singleton(ModuleRegistry::class, function ($app) {
            return new ModuleRegistry($app->make(ModuleDiscovery::class));
        });

        // Enregistrer le registry des menus en singleton
        $this->app->singleton(MenuRegistry::class, function ($app) {
            return new MenuRegistry();
        });

        // Enregistrer le registry de permissions comme singleton
        $this->app->singleton(PermissionRegistry::class, function ($app) {
            return new PermissionRegistry();
        });

        // Merger la configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/license.php',
            'license'
        );

    }

    /**
     * Bootstrap services.
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        \Log::debug('Booting Mercator');

        // Publier la configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/license.php' => config_path('license.php'),
            ], 'mercator-license-config');
        }

        // Publier les migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'mercator-license-migrations');
        }

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Enregistrer le middleware
        // $this->app['router']->aliasMiddleware('license', CheckLicense::class);

        // Enregistrer les directives Blade
        $this->registerBladeDirectives();

        // Enregistrer les Gates
        $this->registerGates();

        // Enregistrer les commandes
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Mercator\Core\Console\Commands\LicenseInstallCommand::class,
                \Mercator\Core\Console\Commands\LicenseCheckCommand::class,
                \Mercator\Core\Console\Commands\LicenseInfoCommand::class,
            ]);
        }

        // Auto-découvrir les modules au boot (si la base est prête)
        $this->autoDiscoverModules();

        // Vérifier la licence au démarrage (en production)
        if ($this->app->environment('production')) {
            $this->checkLicenseOnBoot();
        }
    }

    /**
     * Enregistrer les directives Blade personnalisées
     */
    protected function registerBladeDirectives(): void
    {
        $licenseService = $this->app->make(LicenseService::class);
        $moduleRegistry = $this->app->make(ModuleRegistry::class);

        // @hasLicense
        Blade::if('hasLicense', function () use ($licenseService) {
            return $licenseService->hasValidLicense();
        });

        // @hasModule('bpmn')
        Blade::if('hasModule', function (string $module) use ($licenseService) {
            return $licenseService->hasModule($module);
        });

        // @moduleEnabled('bpmn')
        Blade::if('moduleEnabled', function (string $module) use ($moduleRegistry) {
            return $moduleRegistry->isEnabled($module);
        });

        // @moduleAvailable('bpmn') - Activé ET licencié
        Blade::if('moduleAvailable', function (string $module) use ($moduleRegistry, $licenseService) {
            return $moduleRegistry->isEnabled($module) && $licenseService->hasModuleAccess($module);
        });

        // @isEnterprise
        Blade::if('isEnterprise', function () use ($licenseService) {
            try {
                $type = $licenseService->getLicenseType();
                return in_array($type, ['professional', 'enterprise']);
            } catch (\Exception $e) {
                return false;
            }
        });

        // @licenseExpiringSoon
        Blade::if('licenseExpiringSoon', function (int $days = 30) use ($licenseService) {
            return $licenseService->isExpiringSoon($days);
        });
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
     * Enregistrer les Gates pour les permissions basées sur la licence
     * @throws BindingResolutionException
     */
    protected function registerGates(): void
    {
        $licenseService = $this->app->make(LicenseService::class);
        $moduleRegistry = $this->app->make(ModuleRegistry::class);

        // Gate pour vérifier si un module est disponible (activé ET licencié)
        Gate::define('use-module', function ($user, string $module) use ($licenseService, $moduleRegistry) {
            return $moduleRegistry->isEnabled($module) && $licenseService->hasModuleAccess($module);
        });

        // Gate pour vérifier le type de licence
        Gate::define('has-license-type', function ($user, string $type) use ($licenseService) {
            try {
                return $licenseService->getLicenseType() === $type;
            } catch (\Exception $e) {
                return false;
            }
        });

        // Gate pour les fonctionnalités Enterprise
        Gate::define('use-enterprise-features', function ($user) use ($licenseService) {
            try {
                $type = $licenseService->getLicenseType();
                return in_array($type, ['professional', 'enterprise']);
            } catch (\Exception $e) {
                return false;
            }
        });

        // Gate pour vérifier si un module est activé (indépendamment de la licence)
        Gate::define('module-enabled', function ($user, string $module) use ($moduleRegistry) {
            return $moduleRegistry->isEnabled($module);
        });
    }

    /**
     * Auto-découvrir et installer les nouveaux modules
     */
    protected function autoDiscoverModules(): void
    {
        \Log::debug('Auto-discovering modules');
        try {
            $moduleRegistry = $this->app->make(ModuleRegistry::class);

            // Installer automatiquement les nouveaux modules découverts
            $installed = $moduleRegistry->autoDiscover();

            if (!empty($installed)) {
                \Log::info('Auto-installed modules: ' . implode(', ', $installed));
            }
        } catch (\Exception $e) {
            \Log::debug('Module auto-discovery skipped: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier la licence au démarrage de l'application
     */
    protected function checkLicenseOnBoot(): void
    {
        try {
            $licenseService = $this->app->make(LicenseService::class);

            // Vérification silencieuse
            if (!$licenseService->hasValidLicense()) {
                \Log::warning('Mercator is running without a valid Enterprise license');
            } else {
                $info = $licenseService->getLicenseInfo();

                // Alerter si la licence expire bientôt
                if ($info['status'] === 'expiring_soon') {
                    \Log::warning("License expiring in {$info['days_until_expiration']} days");
                }
            }
        } catch (\Exception $e) {
            \Log::error('License check failed on boot: ' . $e->getMessage());
        }
    }
}