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

];
