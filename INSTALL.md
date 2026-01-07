# Installation de Mercator Core

Ce guide décrit la procédure d'installation et de configuration de Mercator Core.

## Prérequis

- Composer
- Une licence Mercator Enterprise valide

## Installation de la licence

Mercator Core offre trois méthodes pour installer votre licence Enterprise.

### Méthode 1 : Installation depuis une clé de licence (recommandée)

Cette méthode télécharge automatiquement la licence depuis le serveur et **configure automatiquement l'authentification Composer** pour accéder aux modules privés.

```bash
php artisan license:install --key=VOTRE_CLE_DE_LICENCE
```

**Avantages :**
- Téléchargement automatique depuis le serveur
- Configuration automatique de l'authentification Composer
- Validation immédiate de la licence

### Méthode 2 : Installation depuis un fichier

Si vous avez reçu un fichier de licence (`license.json`) :

```bash
php artisan license:install --file=/chemin/vers/license.json
```

**Note :** Cette méthode configure également automatiquement l'authentification Composer.

### Méthode 3 : Installation interactive

Lancez l'assistant d'installation interactif :

```bash
php artisan license:install
```

L'assistant vous guidera à travers les étapes suivantes :
1. Choix de la méthode d'installation (clé ou fichier)
2. Saisie de la clé de licence ou du chemin du fichier
3. Affichage des informations de la licence
4. Confirmation de l'installation
5. Configuration automatique de Composer

### Configuration automatique de Composer

Depuis la version 1.28.0, la commande `license:install` configure automatiquement l'authentification Composer pour accéder au dépôt privé `composer.sourcentis.com`. Cette configuration est nécessaire pour installer les modules Mercator Enterprise.

La commande exécute automatiquement :

```bash
composer config --auth http-basic.composer.sourcentis.com token "VOTRE_CLE_DE_LICENCE"
```

**En cas d'échec de la configuration automatique**, vous verrez un message d'avertissement et devrez exécuter manuellement la commande affichée.

### Validation avec le serveur

Pour valider immédiatement la licence avec le serveur (en plus de la validation locale) :

```bash
php artisan license:install --key=VOTRE_CLE --validate
```

## Vérification de l'installation

### Vérifier le statut de la licence

```bash
php artisan license:status
```

Cette commande affiche :
- Le type de licence
- Le titulaire
- La date d'expiration
- Les modules autorisés
- Le nombre maximum d'utilisateurs

### Vérifier l'authentification Composer

Vous pouvez vérifier que l'authentification Composer est correctement configurée :

```bash
composer config --auth --list
```

Vous devriez voir une entrée pour `http-basic.composer.sourcentis.com`.

## Gestion des modules

Une fois la licence installée, vous pouvez gérer les modules Mercator.

### Lister les modules disponibles

```bash
# Tous les modules
php artisan mercator:module:list

# Seulement les modules installés
php artisan mercator:module:list --installed

# Seulement les modules activés
php artisan mercator:module:list --enabled
```

### Découvrir et installer les modules

```bash
# Découvrir automatiquement les nouveaux modules
php artisan mercator:module:discover

# Installer un module spécifique
php artisan mercator:module:install nom_du_module
```

### Activer/Désactiver un module

```bash
# Activer un module
php artisan mercator:module:enable nom_du_module

# Désactiver un module
php artisan mercator:module:disable nom_du_module

# Voir le statut d'un module
php artisan mercator:module:status nom_du_module
```

## Dépannage

### Erreur d'authentification Composer

Si vous rencontrez des erreurs lors de l'installation de modules (erreur 401 ou 403), vérifiez que :

1. Votre licence est valide :
   ```bash
   php artisan license:status
   ```

2. L'authentification Composer est configurée :
   ```bash
   composer config --auth --list
   ```

3. Si nécessaire, reconfigurez manuellement :
   ```bash
   composer config --auth http-basic.composer.sourcentis.com token "VOTRE_CLE"
   ```

### Problèmes de validation de licence

Si la validation de la licence échoue :

1. Vérifiez que l'URL du serveur de licences est correcte dans `.env`
2. Vérifiez que le serveur est accessible depuis votre environnement
3. Vérifiez que la date système est correcte (pour la validation d'expiration)
4. Consultez les logs : `storage/logs/laravel.log`

### Cache de licence

Le système met en cache les informations de licence pour améliorer les performances. Si vous rencontrez des problèmes après une mise à jour de licence :

```bash
php artisan cache:clear
php artisan config:clear
```

## Support

Pour obtenir de l'aide :
- Documentation : https://docs.sourcentis.com
- Support : support@sourcentis.com
- Issues GitHub : https://github.com/sourcentis/mercator-core/issues

## Licence

Mercator Core est distribué sous licence AGPL-3.0. Une licence Enterprise est requise pour utiliser les modules propriétaires.
