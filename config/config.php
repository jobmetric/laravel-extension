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
        'ShippingMethod' => [
            'label' => 'extension::base.types.shipping_method.label',
            'description' => 'extension::base.types.shipping_method.description',
        ],
        'PaymentMethod' => [
            'label' => 'extension::base.types.payment_method.label',
            'description' => 'extension::base.types.payment_method.description',
        ],
        'OnlinePaymentMethod' => [
            'label' => 'extension::base.types.online_payment_method.label',
            'description' => 'extension::base.types.online_payment_method.description',
        ],
        'OrderTotal' => [
            'label' => 'extension::base.types.order_total.label',
            'description' => 'extension::base.types.order_total.description',
        ],
        'ReturnOrderTotal' => [
            'label' => 'extension::base.types.return_order_total.label',
            'description' => 'extension::base.types.return_order_total.description',
        ],
        'Captcha' => [
            'label' => 'extension::base.types.captcha.label',
            'description' => 'extension::base.types.captcha.description',
        ],
        'Report' => [
            'label' => 'extension::base.types.report.label',
            'description' => 'extension::base.types.report.description',
        ],
        'Theme' => [
            'label' => 'extension::base.types.theme.label',
            'description' => 'extension::base.types.theme.description',
        ],
        'AttendanceDevice' => [
            'label' => 'extension::base.types.attendance_device.label',
            'description' => 'extension::base.types.attendance_device.description',
        ],
        'SyncingSoftware' => [
            'label' => 'extension::base.types.syncing_software.label',
            'description' => 'extension::base.types.syncing_software.description',
        ],
    ],

];
