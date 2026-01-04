<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercator:module:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and install new Mercator modules automatically';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $this->info('Discovering modules...');

        $installed = $registry->autoDiscover();

        if (empty($installed)) {
            $this->info('No new modules to install.');
            return self::SUCCESS;
        }

        $this->info('Installed ' . count($installed) . ' module(s):');
        foreach ($installed as $moduleName) {
            $this->line("  - {$moduleName}");
            
            // Synchroniser les permissions
            $registry->syncPermissions($moduleName);
        }

        return self::SUCCESS;
    }
}

