<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Extension Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Extension for
    | various messages that we need to display to the user.
    |
    */

    "validation" => [
        "namespace_not_found" => "The :namespace file not found.",
    ],

    "exceptions" => [
        "extension_already_installed" => "The :name extension is already installed.",
        "extension_not_installed" => "The :name extension is not installed.",
        "extension_not_uninstalled" => "The :name extension is not uninstalled.",
        "extension_not_deletable" => "The :name extension is not deletable.",
        "extension_folder_not_found" => "The :name extension folder not found.",
        "extension_runner_not_found" => "The :name extension runner not found.",
        "extension_config_file_not_found" => "The :name extension config file not found.",
        "extension_configuration_not_match" => "The :name extension configuration not match.",
        "extension_class_name_not_match" => "The :name extension class name not match.",
        "extension_dont_have_contract" => "The :name extension does not extend AbstractExtension.",
        "extension_have_some_plugin" => "The :name extension have some plugin. Please delete the plugins first.",
        "extension_not_found" => "The extension not found.",
        "plugin_not_found" => "The plugin with ID :plugin_id not found.",
        "plugin_not_multiple" => "The :extension/:name extension dont have multiple plugin.",
        "plugin_not_match_extension" => "The plugin :plugin_id not match with extension :extension_id.",
    ],

    "messages" => [
        "extension" => [
            "installed" => "The :name extension has been installed.",
            "uninstalled" => "The :name extension has been uninstalled.",
            "deleted" => "The :name extension has been deleted.",
            "updated" => "The :name extension has been updated.",
        ],

        "plugin" => [
            "stored" => "The plugin has been stored successfully.",
            "updated" => "The plugin has been updated successfully.",
            "deleted_items" => "{1} One plugin item from :extension extension has been deleted successfully.|[2,*] :count plugin items from :extension extension have been deleted successfully.",
            "added" => "The plugin has been added.",
            "edited" => "The plugin has been edited.",
            "deleted" => "The plugin has been deleted.",
        ],
    ],

    "fields" => [
        "title" => [
            "label" => "Title",
            "info" => "Enter the name of your plugin.",
            "placeholder" => "Enter plugin title.",
        ],
        "status" => [
            "label" => "Status",
            "info" => "Select plugin status for enable or disable.",
        ]
    ],

    "list" => [
        "extension" => [
            "message" => [
                "confirm" => [
                    "uninstall" => "Are you sure to uninstall?",
                    "delete" => "Are you sure to delete extension files? This action is irreversible.",
                ],
            ],
            "columns" => [
                "name" => "Extension Name",
                "version" => "Version",
                "author" => "Author",
                "installed_at" => "Installed At",
                "website" => "Website",
                "email" => "Email",
                "namespace" => "Extension File Path",
                "license" => "License",
                "delete_note" => "Note: Deleting an extension will delete all plugins associated with it.",
                "delete" => "Delete Extension",
                "creation_at" => "Extension Creation Date",
                "updated_at" => "Extension Updated Date",
                "not_installed" => "Not Installed",
                "simple" => "Simple",
                "multiple" => "Multiple",
            ],
            "buttons" => [
                "install" => "Install",
                "uninstall" => "Uninstall",
                "add_plugin" => "Add Plugin",
                "plugin_list" => "Plugins",
                "are_you_sure_to_uninstall" => "Yes, I'm sure to uninstall",
                "are_you_sure_to_delete" => "Yes, I'm sure to delete",
            ],
        ],
        "plugin" => [
            "label" => "Plugins of :name Extension",
            "description" => "You can manage the plugins of the :name extension here.",
            "filters" => [
                "name" => [
                    "title" => "Plugin Name",
                    "placeholder" => "Enter plugin name",
                ]
            ],
        ],
    ],

    "form" => [
        "plugin" => [
            "create" => [
                "title" => "Create Plugin of :name Extension",
            ],
            "edit" => [
                "title" => "Edit Plugin Number :number of :name Extension",
            ],
            "fields" => [
                "name" => [
                    "title" => "Plugin Name",
                    "placeholder" => "Enter plugin name",
                ],
            ],
        ],
    ],

    "extension" => [
        "default_description" => "This is a test extension. 😉",
    ],

    "types" => [
        "module" => [
            "label" => "Module",
            "description" => "Extensions that add modules and features to the system.",
        ],
        "shipping_method" => [
            "label" => "Shipping Method",
            "description" => "Extensions that define shipping methods for orders.",
        ],
        "payment_method" => [
            "label" => "Payment Method",
            "description" => "Extensions that define payment methods for orders.",
        ],
        "online_payment_method" => [
            "label" => "Online Payment Method",
            "description" => "Extensions that define online payment gateways.",
        ],
        "order_total" => [
            "label" => "Order Total",
            "description" => "Extensions that modify or add order total calculations.",
        ],
        "return_order_total" => [
            "label" => "Return Order Total",
            "description" => "Extensions that modify or add return order total calculations.",
        ],
        "captcha" => [
            "label" => "Captcha",
            "description" => "Extensions that provide captcha or human verification.",
        ],
        "report" => [
            "label" => "Report",
            "description" => "Extensions that add reports and analytics.",
        ],
        "theme" => [
            "label" => "Theme",
            "description" => "Extensions that define themes and layouts.",
        ],
        "attendance_device" => [
            "label" => "Attendance Device",
            "description" => "Extensions that integrate with attendance devices.",
        ],
        "syncing_software" => [
            "label" => "Syncing Software",
            "description" => "Extensions that sync data with external software.",
        ],
    ],

    "component" => [
        "plugin_field" => [
            "title" => "Plugin",
            "placeholder" => "Select plugin.",
        ],
    ],

];
