# Mercator Core

The Mercator Core project contains Mercator core services 
(modules, licensing, menus, domain models)


# Notes de version

## Release

cd /path/vers/mercator-core
git checkout main
git pull

Create a new tag

    git tag
    git tag v1.0.1
    git push origin v1.0.1


## Update Composer

Force la mise à jour :

    composer clear-cache
    composer update sourcentis/mercator-core


Vérifie que Composer prend bien la bonne version :

    composer show sourcentis/mercator-core

# version : v1.0.1


php artisan optimize:clear
php artisan package:discover


