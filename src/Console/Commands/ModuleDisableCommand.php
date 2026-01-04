<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercator:module:disable
                            {module : The name of the module to disable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a Mercator module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');

        // Vérifier que le module est installé
        if (!$registry->isInstalled($moduleName)) {
            $this->error("Module '{$moduleName}' is not installed.");
            return self::FAILURE;
        }

        // Désactiver le module
        if ($registry->disable($moduleName)) {
            $this->info("Module '{$moduleName}' disabled successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to disable module '{$moduleName}'.");
        return self::FAILURE;
    }
}

