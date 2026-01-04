<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercator:module:status
                            {module : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of a Mercator module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');
        $status = $registry->getStatus($moduleName);

        if ($status === null) {
            $this->error("Module '{$moduleName}' not found.");
            return self::FAILURE;
        }

        $this->info("Module: {$moduleName}");
        $this->line("Label: " . ($status['meta']['label'] ?? 'N/A'));
        $this->line("Version: " . $status['version']);
        $this->line("Discovered: " . ($status['discovered'] ? 'Yes' : 'No'));
        $this->line("Installed: " . ($status['installed'] ? 'Yes' : 'No'));
        $this->line("Enabled: " . ($status['enabled'] ? 'Yes' : 'No'));

        return self::SUCCESS;
    }
}

