<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;

class ModuleListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercator:module:list
                            {--installed : Show only installed modules}
                            {--enabled : Show only enabled modules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Mercator modules';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $modules = $registry->getAllWithMeta();

        if (empty($modules)) {
            $this->info('No modules found.');
            return self::SUCCESS;
        }

        // Filtrer selon les options
        if ($this->option('installed')) {
            $modules = array_filter($modules, fn($m) => $m['installed']);
        }

        if ($this->option('enabled')) {
            $modules = array_filter($modules, fn($m) => $m['enabled']);
        }

        // Préparer les données pour le tableau
        $rows = [];
        foreach ($modules as $name => $module) {
            $rows[] = [
                $name,
                $module['meta']['label'] ?? $name,
                $module['version'],
                $module['installed'] ? '✓' : '✗',
                $module['enabled'] ? '✓' : '✗',
            ];
        }

        $this->table(
            ['Name', 'Label', 'Version', 'Installed', 'Enabled'],
            $rows
        );

        return self::SUCCESS;
    }
}
