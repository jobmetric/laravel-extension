<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'extension' => 'extensions',
        'extension_migration' => 'extension_migrations',
        'plugin' => 'plugins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension Types (Registry defaults)
    |--------------------------------------------------------------------------
    |
    | Types registered by default in ExtensionTypeRegistry. You can register
    | more at runtime via ExtensionTypeRegistry::register() or in a service
    | provider. Each key is the type name, value is optional options array.
    */

    'types' => [
        'Module' => [
            'label' => 'extension::base.types.module.label',
            'description' => 'extension::base.types.module.description',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Discover Cache
    |--------------------------------------------------------------------------
    |
    | Cache discovered extension list to avoid filesystem scan on every request.
    | TTL in seconds; 0 = disabled.
    |
    */

    'discover_cache_ttl' => env('EXTENSION_DISCOVER_CACHE_TTL', 0),
    'discover_cache_key' => 'extension_kernel.discovered',

];
