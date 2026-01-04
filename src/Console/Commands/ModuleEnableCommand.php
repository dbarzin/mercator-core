<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercator:module:enable
                            {module : The name of the module to enable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable a Mercator module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');

        // Vérifier que le module existe
        if (!$registry->exists($moduleName)) {
            $this->error("Module '{$moduleName}' not found.");
            return self::FAILURE;
        }

        // Activer le module
        if ($registry->enable($moduleName)) {
            $this->info("Module '{$moduleName}' enabled successfully.");
            
            // Synchroniser les permissions
            $registry->syncPermissions($moduleName);
            $this->info("Permissions synced.");
            
            return self::SUCCESS;
        }

        $this->error("Failed to enable module '{$moduleName}'.");
        return self::FAILURE;
    }
}
