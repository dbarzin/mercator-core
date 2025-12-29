<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Services\LicenseService;

class LicenseInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:info 
                            {--json : Output as JSON}
                            {--modules : Show available modules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display detailed license information';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        try {
            $info = $licenseService->getLicenseInfo();

            // Output JSON
            if ($this->option('json')) {
                $this->line(json_encode($info, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            }

            // Output normal
            $this->displayLicenseInfo($info);

            // Show modules if requested
            if ($this->option('modules')) {
                $this->newLine(2);
                $this->displayModules($info);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Unable to retrieve license information: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Display license information
     */
    protected function displayLicenseInfo(array $info): void
    {
        $this->info('===========================================');
        $this->info('  Mercator Enterprise - License Details');
        $this->info('===========================================');
        $this->newLine();

        if (!$info['valid']) {
            $this->error('⚠ No valid license found');
            if (isset($info['error'])) {
                $this->error('Error: ' . $info['error']);
            }
            $this->newLine();
            $this->warn('To install a license:');
            $this->warn('  php artisan license:install --key=YOUR-LICENSE-KEY');
            return;
        }

        // License status
        $statusColor = match($info['status']) {
            'active', 'perpetual' => 'green',
            'expiring_soon' => 'yellow',
            'expired' => 'red',
            default => 'gray',
        };

        $this->line("Status: <fg={$statusColor}>" . ucfirst(str_replace('_', ' ', $info['status'])) . '</>' );

        // Basic information
        $this->newLine();
        $this->table(
            ['Property', 'Value'],
            [
                ['License Type', $this->formatLicenseType($info['type'])],
                ['Issued To', $info['issued_to']],
                ['License Key', $info['license_key']],
                ['', ''],
                ['Issued Date', $info['issued_date']],
                ['Expiration Date', $info['expiration_date'] ?? '<fg=green>Never (Perpetual)</>' ],
                ['Days Until Expiration', $this->formatDaysRemaining($info)],
                ['', ''],
                ['Maximum Users', $info['max_users']],
                ['Included Modules', count($info['modules'])],
            ]
        );

        // Warnings or messages
        $this->displayWarnings($info);
    }

    /**
     * Display available modules
     */
    protected function displayModules(array $info): void
    {
        $this->info('Available Modules:');
        $this->newLine();

        if (empty($info['modules'])) {
            $this->warn('No Enterprise modules included in this license');
            return;
        }

        $allModules = config('license.enterprise_modules', []);
        $rows = [];

        foreach ($info['modules'] as $moduleKey) {
            $module = $allModules[$moduleKey] ?? null;

            $rows[] = [
                '✓',
                strtoupper($moduleKey),
                $module['name'] ?? 'Unknown',
                $module['description'] ?? 'No description',
            ];
        }

        $this->table(
            ['', 'Key', 'Name', 'Description'],
            $rows
        );

        // Show unavailable modules
        $unavailable = array_diff(array_keys($allModules), $info['modules']);

        if (!empty($unavailable)) {
            $this->newLine();
            $this->comment('Modules not included in your license:');

            foreach ($unavailable as $moduleKey) {
                $module = $allModules[$moduleKey];
                $this->line("  ✗ {$module['name']} ({$moduleKey})");
            }

            $this->newLine();
            $this->comment('To upgrade your license, contact sales@mercator-enterprise.com');
        }
    }

    /**
     * Display warnings based on license status
     */
    protected function displayWarnings(array $info): void
    {
        $this->newLine();

        if ($info['status'] === 'expired') {
            $this->error('╔════════════════════════════════════════════════════╗');
            $this->error('║  ⚠ LICENSE EXPIRED                                 ║');
            $this->error('╚════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->error("Your license expired {$info['days_until_expiration']} days ago.");
            $this->error('Enterprise features are disabled.');
            $this->error('Contact: support@mercator-enterprise.com');

        } elseif ($info['status'] === 'expiring_soon') {
            $this->warn('╔════════════════════════════════════════════════════╗');
            $this->warn('║  ⚠ LICENSE EXPIRING SOON                           ║');
            $this->warn('╚════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->warn("Your license will expire in {$info['days_until_expiration']} days.");
            $this->warn('Please renew before expiration to avoid service interruption.');
            $this->warn('Contact: support@mercator-enterprise.com');

        } elseif ($info['status'] === 'perpetual') {
            $this->info('This is a perpetual license with no expiration date.');

        } else {
            $this->info('Your license is active and valid.');
        }
    }

    /**
     * Format license type with color
     */
    protected function formatLicenseType(string $type): string
    {
        return match($type) {
            'enterprise' => '<fg=blue>Enterprise</>',
            'professional' => '<fg=cyan>Professional</>',
            'community' => '<fg=gray>Community</>',
            default => ucfirst($type),
        };
    }

    /**
     * Format days remaining
     */
    protected function formatDaysRemaining(array $info): string
    {
        if ($info['days_until_expiration'] === null) {
            return '<fg=green>∞ (Perpetual)</>';
        }

        $days = $info['days_until_expiration'];

        if ($days < 0) {
            return "<fg=red>Expired {$days} days ago</>";
        } elseif ($days < 30) {
            return "<fg=yellow>{$days} days</>";
        } else {
            return "<fg=green>{$days} days</>";
        }
    }
}