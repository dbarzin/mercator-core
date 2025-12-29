<?php

namespace Mercator\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Mercator\Core\Services\LicenseService;

class LicenseInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:install 
                            {--key= : License key}
                            {--file= : Path to license file}
                            {--validate : Validate with server immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a Mercator Enterprise license';

    protected LicenseService $licenseService;

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        $this->licenseService = $licenseService;

        $this->info('===========================================');
        $this->info('  Mercator Enterprise - License Installer');
        $this->info('===========================================');
        $this->newLine();

        try {
            // Méthode 1 : Installation depuis une clé
            if ($key = $this->option('key')) {
                return $this->installFromKey($key);
            }

            // Méthode 2 : Installation depuis un fichier
            if ($file = $this->option('file')) {
                return $this->installFromFile($file);
            }

            // Mode interactif
            return $this->interactiveInstall();

        } catch (Exception $e) {
            $this->error('License installation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Installation depuis une clé de licence
     */
    protected function installFromKey(string $key): int
    {
        $this->info("Installing license from key...");

        // Télécharger la licence depuis le serveur
        $licenseServer = config('license.server_url');

        if (!$licenseServer) {
            $this->error('License server URL not configured');
            return Command::FAILURE;
        }

        $this->info("Fetching license from server...");

        try {
            $response = Http::timeout(30)->get("{$licenseServer}/api/v1/licenses/download", [
                'key' => $key,
            ]);

            if (!$response->successful()) {
                $this->error('Unable to fetch license from server');
                $this->error('Response: ' . $response->body());
                return Command::FAILURE;
            }

            $licenseData = $response->json();

            return $this->saveLicense($licenseData);

        } catch (Exception $e) {
            $this->error('Failed to download license: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Installation depuis un fichier
     */
    protected function installFromFile(string $filePath): int
    {
        $this->info("Installing license from file: {$filePath}");

        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $content = File::get($filePath);
        $licenseData = json_decode($content, true);

        if ($licenseData === null) {
            $this->error('Invalid JSON in license file');
            return Command::FAILURE;
        }

        return $this->saveLicense($licenseData);
    }

    /**
     * Installation interactive
     */
    protected function interactiveInstall(): int
    {
        $this->warn('No license key or file provided. Starting interactive installation...');
        $this->newLine();

        $method = $this->choice(
            'How would you like to install the license?',
            ['key' => 'License Key', 'file' => 'License File'],
            'key'
        );

        if ($method === 'key') {
            $key = $this->ask('Enter your license key');
            return $this->installFromKey($key);
        } else {
            $filePath = $this->ask('Enter the path to your license file');
            return $this->installFromFile($filePath);
        }
    }

    /**
     * Sauvegarder la licence
     */
    protected function saveLicense(array $licenseData): int
    {
        // Valider la structure
        $requiredFields = ['license_key', 'type', 'issued_to', 'issued_date', 'signature'];
        foreach ($requiredFields as $field) {
            if (!isset($licenseData[$field])) {
                $this->error("Invalid license: missing field '{$field}'");
                return Command::FAILURE;
            }
        }

        // Afficher les informations
        $this->newLine();
        $this->info('License Information:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Type', $licenseData['type']],
                ['Issued To', $licenseData['issued_to']],
                ['Issued Date', $licenseData['issued_date']],
                ['Expiration', $licenseData['expiration_date'] ?? 'Never'],
                ['Modules', implode(', ', $licenseData['modules'] ?? [])],
                ['Max Users', $licenseData['max_users'] ?? 'Unlimited'],
            ]
        );
        $this->newLine();

        // Confirmer
        if (!$this->confirm('Do you want to install this license?', true)) {
            $this->info('Installation cancelled');
            return Command::SUCCESS;
        }

        // Créer le répertoire si nécessaire
        $licenseDir = storage_path('app/license');
        if (!File::isDirectory($licenseDir)) {
            File::makeDirectory($licenseDir, 0755, true);
        }

        // Sauvegarder le fichier
        $licensePath = $licenseDir . '/license.json';
        File::put($licensePath, json_encode($licenseData, JSON_PRETTY_PRINT));
        File::chmod($licensePath, 0600);

        $this->info('✓ License file saved');

        // Invalider le cache
        $this->licenseService->clearCache();

        // Valider la licence
        $this->newLine();
        $this->info('Validating license...');

        if (!$this->licenseService->hasValidLicense()) {
            $this->error('✗ License validation failed');
            $this->warn('The license was saved but is not valid. Please check:');
            $this->warn('  - The license signature');
            $this->warn('  - The expiration date');
            $this->warn('  - The license server configuration');
            return Command::FAILURE;
        }

        $this->info('✓ License is valid');

        // Validation serveur optionnelle
        if ($this->option('validate')) {
            $this->newLine();
            $this->info('Validating with license server...');

            if ($this->licenseService->revalidate()) {
                $this->info('✓ Server validation successful');
            } else {
                $this->warn('⚠ Server validation failed (license may still work offline)');
            }
        }

        $this->newLine();
        $this->info('===========================================');
        $this->info('✓ License installed successfully!');
        $this->info('===========================================');

        return Command::SUCCESS;
    }
}
