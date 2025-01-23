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
        "errors" => "Validation errors occurred.",
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
        "extension_dont_have_contract" => "The :name extension dont have JobMetric\Extension\Contracts\ExtensionContract contract.",
        "extension_have_some_plugin" => "The :name extension have some plugin. Please uninstall the plugin first.",
        "plugin_not_found" => "The plugin with ID :plugin_id not found.",
        "plugin_not_multiple" => "The :extension/:name extension dont have multiple plugin.",
    ],

    "messages" => [
        "extension" => [
            "installed" => "The :name extension has been installed.",
            "uninstalled" => "The :name extension has been uninstalled.",
            "deleted" => "The :name extension has been deleted.",
            "updated" => "The :name extension has been updated.",
        ],

        "plugin" => [
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
            "add_plugin" => "افزودن پلاگین",
            "plugin_list" => "لیست پلاگین ها",
        ],
    ],

    "extension" => [
        "default_description" => "This is a test extension. 😉",
    ],

];
