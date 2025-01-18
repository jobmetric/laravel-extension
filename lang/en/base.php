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
    ],

    "exceptions" => [
        "extension_already_installed" => "The :extension/:name extension is already installed.",
        "extension_not_installed" => "The :extension/:name extension is not installed.",
        "extension_folder_not_found" => "The :extension/:name extension folder not found.",
        "extension_runner_not_found" => "The :extension/:name extension runner not found.",
        "extension_config_file_not_found" => "The :extension/:name extension config file not found.",
        "extension_configuration_not_match" => "The :extension/:name extension configuration not match.",
        "extension_class_name_not_match" => "The :extension/:name extension class name not match.",
        "extension_dont_have_contract" => "The :extension/:name extension dont have JobMetric\Extension\Contracts\ExtensionContract contract.",
        "extension_have_some_plugin" => "The :extension extension have some plugin. Please uninstall the plugin first.",
        "extension_type_invalid" => "The :extension/:name extension type is invalid.",
        "plugin_not_found" => "The plugin with ID :plugin_id not found.",
        "plugin_not_multiple" => "The :extension/:name extension dont have multiple plugin.",
    ],

    "messages" => [
        "extension" => [
            "installed" => "The :extension/:name extension has been installed.",
            "uninstalled" => "The :extension/:name extension has been uninstalled.",
            "updated" => "The :extension/:name extension has been updated.",
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
        ],
        "buttons" => [
            "install" => "Install",
            "uninstall" => "Uninstall",
        ],
    ],

    "extension" => [
        "default_description" => "This is a test extension. 😉",
    ],

];
