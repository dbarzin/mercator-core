<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Modules\ModuleRegistry;
use Mercator\Core\Modules\ModuleDiscovery;

class ModuleDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:discover 
                            {--clear-cache : Clear the module cache before discovering}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and auto-install new Mercator modules from Composer packages';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry, ModuleDiscovery $discovery): int
    {
        $this->info('Discovering Mercator modules...');
        $this->newLine();

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $registry->clearCache();
            $this->info('✓ Cache cleared');
            $this->newLine();
        }

        try {
            // Découvrir les modules
            $discovered = $discovery->discover();

            if (empty($discovered)) {
                $this->warn('No Mercator modules found in Composer packages.');
                $this->newLine();
                $this->info('To create a Mercator module, add this to your package composer.json:');
                $this->line('');
                $this->line('"extra": {');
                $this->line('  "mercator-module": {');
                $this->line('    "name": "your-module-name",');
                $this->line('    "label": "Your Module Label",');
                $this->line('    "description": "Module description",');
                $this->line('    "version": "1.0.0",');
                $this->line('    "provider": "Your\\\\Namespace\\\\ServiceProvider"');
                $this->line('  }');
                $this->line('}');
                return Command::SUCCESS;
            }

            $this->info("Found {$discovery->count()} module(s):");
            $this->newLine();

            // Afficher les modules découverts
            $rows = [];
            foreach ($discovered as $name => $meta) {
                $status = $registry->getStatus($name);

                $rows[] = [
                    $name,
                    $meta['label'],
                    $meta['package'],
                    $meta['version'],
                    $status['installed'] ? '✓' : '✗',
                    $status['enabled'] ? '<fg=green>Yes</>' : '<fg=red>No</>',
                ];
            }

            $this->table(
                ['Name', 'Label', 'Package', 'Version', 'Installed', 'Enabled'],
                $rows
            );

            // Auto-installer les nouveaux modules
            $this->newLine();
            if ($this->confirm('Auto-install new modules?', true)) {
                $installed = $registry->autoDiscover();

                if (!empty($installed)) {
                    $this->info('✓ Installed ' . count($installed) . ' new module(s):');
                    foreach ($installed as $name) {
                        $this->line("  • {$name}");
                    }
                } else {
                    $this->info('✓ All discovered modules are already installed');
                }
            }

            $this->newLine();
            $this->info('✓ Module discovery complete!');
            $this->newLine();
            $this->comment('To manage modules, visit: ' . url('/admin/modules'));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Module discovery failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}