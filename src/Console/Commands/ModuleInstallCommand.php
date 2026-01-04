<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercator:module:install
                            {module : The name of the module to install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a Mercator module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');

        // Vérifier que le module existe
        if (!$registry->exists($moduleName)) {
            $this->error("Module '{$moduleName}' not found in Composer packages.");
            return self::FAILURE;
        }

        // Vérifier si déjà installé
        if ($registry->isInstalled($moduleName)) {
            $this->warn("Module '{$moduleName}' is already installed.");
            
            if (!$this->confirm('Do you want to reinstall it?')) {
                return self::SUCCESS;
            }
        }

        // Installer le module
        $registry->install($moduleName);
        $this->info("Module '{$moduleName}' installed successfully.");
        
        // Synchroniser les permissions
        $registry->syncPermissions($moduleName);
        $this->info("Permissions synced.");

        return self::SUCCESS;
    }
}

