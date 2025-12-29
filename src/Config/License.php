<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Server URL
    |--------------------------------------------------------------------------
    |
    | L'URL du serveur de licences Mercator-License pour la validation en ligne.
    | Laisser null pour désactiver la validation serveur.
    |
    */

    'server_url' => env('MERCATOR_LICENSE_SERVER', 'https://license.sourcentis.com'),

    /*
    |--------------------------------------------------------------------------
    | Public Key
    |--------------------------------------------------------------------------
    |
    | La clé publique RSA pour vérifier les signatures de licence.
    | Cette clé doit correspondre à la clé privée utilisée par Mercator-License.
    |
    */

    'public_key' => <<<'EOD'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA31Hirm6Vdrhfxu9iR3cb
0P1XE5oS6sdKJRk3x3szuBd/TDjbS5GSxS8J/uyu08eJoV2LbgPsN9qc7CfwQodM
38S+1HZ2WNcfrgL51kug89bE06rxiazqGCzmQ/gg+HqA1Vb0YP5uxTklpooXxM3j
ci1X7uBkjVwjePqr83TbpZUtKv3EVZzK5MHxB1RHrjhbu/nPMSK+lDzsRdtCYRix
Md42EPU+BHsRIS+gOji1ma+xofgkWTafPEgxLDvl+y4SraG3JCHbT7a9vypdgCuP
JpdoPhmPRiQB+8vUzRwt66+aLqeDuyB/j1a+rhDvY8kZUHiReKaBTQ6961DiLp+R
3QIDAQAB
-----END PUBLIC KEY-----
EOD,

    /*
    |--------------------------------------------------------------------------
    | Offline Mode
    |--------------------------------------------------------------------------
    |
    | Si true, la validation serveur sera désactivée et seule la validation
    | locale (signature + expiration) sera effectuée.
    |
    */

    'offline_mode' => env('MERCATOR_LICENSE_OFFLINE', false),

    /*
    |--------------------------------------------------------------------------
    | Grace Period (Days)
    |--------------------------------------------------------------------------
    |
    | Nombre de jours après expiration pendant lesquels la licence reste
    | valide (période de grâce).
    |
    */

    'grace_period_days' => env('MERCATOR_LICENSE_GRACE_PERIOD', 30),

    /*
    |--------------------------------------------------------------------------
    | Telemetry
    |--------------------------------------------------------------------------
    |
    | Si true, des informations d'utilisation anonymes seront envoyées
    | au serveur de licences lors de la validation.
    |
    */

    'telemetry_enabled' => env('MERCATOR_LICENSE_TELEMETRY', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration (Seconds)
    |--------------------------------------------------------------------------
    |
    | Durée de mise en cache des informations de licence (en secondes).
    | Par défaut : 24 heures (86400 secondes)
    |
    */

    'cache_duration' => env('MERCATOR_LICENSE_CACHE_DURATION', 86400),

    /*
    |--------------------------------------------------------------------------
    | Modules Enterprise
    |--------------------------------------------------------------------------
    |
    | Liste des modules Enterprise disponibles.
    |
    */

    'enterprise_modules' => [
        'bpmn' => [
            'name' => 'BPMN Editor',
            'description' => 'Advanced BPMN 2.0 process modeling',
            'routes' => [
                'bpmn.*',
                'api.bpmn.*',
            ],
        ],
        'analytics' => [
            'name' => 'Advanced Analytics',
            'description' => 'Business intelligence and reporting',
            'routes' => [
                'analytics.*',
            ],
        ],
        'api' => [
            'name' => 'Extended API',
            'description' => 'Full REST API access',
            'routes' => [
                'api.*',
            ],
        ],
    ],

];