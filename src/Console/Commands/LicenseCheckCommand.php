<?php

namespace Mercator\Core\Console\Commands;

use Illuminate\Console\Command;
use Mercator\Core\Services\LicenseService;

class LicenseCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:check 
                            {--server : Force server validation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the Mercator license is valid';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        $quiet = $this->option('quiet');

        if (!$quiet) {
            $this->info('Checking Mercator license...');
            $this->newLine();
        }

        try {
            // Invalider le cache si validation serveur demandée
            if ($this->option('server')) {
                $licenseService->clearCache();
            }

            // Vérifier la licence
            $isValid = $licenseService->hasValidLicense(true);

            if (!$quiet) {
                if ($isValid) {
                    $this->info('✓ License signature is valid');

                    // Afficher les détails
                    $info = $licenseService->getLicenseInfo();

                    $this->newLine();
                    $this->table(
                        ['Property', 'Value'],
                        [
                            ['Status', $this->getStatusLabel($info['status'])],
                            ['Type', ucfirst($info['type'])],
                            ['Issued To', $info['issued_to']],
                            ['License Key', $info['license_key']],
                            ['Issued Date', $info['issued_date']],
                            ['Expiration Date', $info['expiration_date'] ?? 'Never'],
                            ['Days Until Expiration', $info['days_until_expiration'] ?? 'N/A'],
                            ['Modules', implode(', ', $info['modules'])],
                            ['Max Users', $info['max_users']],
                        ]
                    );

                    $this->newLine();

                    // Avertissements
                    if ($info['status'] === 'expiring_soon') {
                        $this->warn("⚠ License will expire in {$info['days_until_expiration']} days!");
                        $this->warn('Please contact support@sourcentis.com to renew.');
                    } elseif ($info['status'] === 'expired') {
                        $this->error("✗ License expired {$info['days_until_expiration']} days ago!");
                        $this->error('Please contact support@sourcentis.com immediately.');
                    }

                    $this->newLine();

                    // Validation avec le serveur de licences
                    if ($licenseService->validateWithServer()) {
                        $this->info('✓ License has been validated with the Licence server.');
                    } else {
                        $this->error("✗ License could not be verified with the Licence server !");
                    }

                } else {
                    $this->error('✗ No valid license found');
                    $this->newLine();
                    $this->warn('Mercator Enterprise features are disabled.');
                    $this->warn('To install a license, run: php artisan license:install');
                }
            }

            return $isValid ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            if (!$quiet) {
                $this->error('License check failed: ' . $e->getMessage());
            }
            return Command::FAILURE;
        }
    }

    /**
     * Get a colored status label
     */
    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'active' => '<fg=green>Active</>',
            'perpetual' => '<fg=green>Perpetual</>',
            'expiring_soon' => '<fg=yellow>Expiring Soon</>',
            'expired' => '<fg=red>Expired</>',
            default => '<fg=gray>Unknown</>',
        };
    }
}