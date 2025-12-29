<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize available modules with the database';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $moduleRegistry): int
    {
        $this->info('Synchronizing modules...');
        $this->newLine();

        try {
            $moduleRegistry->sync();

            $status = $moduleRegistry->getAllModulesStatus();

            $this->info('Module Status:');
            $this->newLine();

            $rows = [];
            foreach ($status as $key => $moduleStatus) {
                $rows[] = [
                    $key,
                    $moduleStatus['info']['name'] ?? 'Unknown',
                    $moduleStatus['enabled'] ? '✓' : '✗',
                    $moduleStatus['licensed'] ? '✓' : '✗',
                    $moduleStatus['available'] ? '<fg=green>Available</>' : '<fg=red>Unavailable</>',
                ];
            }

            $this->table(
                ['Key', 'Name', 'Enabled', 'Licensed', 'Status'],
                $rows
            );

            $this->newLine();
            $this->info('✓ Modules synchronized successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to synchronize modules: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
