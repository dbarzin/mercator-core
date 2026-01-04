# Mercator Core

The Mercator Core project contains Mercator core services 
(modules, licensing, menus, domain models)

# Commandes

## Lister tous les modules
php artisan mercator:module:list

## Lister seulement les modules installés
php artisan mercator:module:list --installed

## Lister seulement les modules activés
php artisan mercator:module:list --enabled

## Afficher le statut d'un module
php artisan mercator:module:status bpmn

## Découvrir et installer automatiquement les nouveaux modules
php artisan mercator:module:discover

## Installer un module spécifique
php artisan mercator:module:install bpmn

## Activer un module
php artisan mercator:module:enable bpmn

## Désactiver un module
php artisan mercator:module:disable bpmn


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



