# Mercator Core

Core services for Mercator: module management, licensing, menus, and domain models.

## Commands

### List modules

```bash
# List all available modules
php artisan mercator:module:list

# List only installed modules
php artisan mercator:module:list --installed

# List only enabled modules
php artisan mercator:module:list --enabled
```

### Manage modules

```bash
# Show module status
php artisan mercator:module:status module_name

# Discover and install new modules automatically
php artisan mercator:module:discover

# Install a specific module
php artisan mercator:module:install module_name

# Enable a module
php artisan mercator:module:enable module_name

# Disable a module
php artisan mercator:module:disable module_name
```

## Requirements

- PHP >= 8.3
- Laravel >= 10.0

## License

AGPL-3.0