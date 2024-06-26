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

    'validation' => [
        'errors' => 'Validation errors occurred.',
    ],

    'exceptions' => [
        'extension_already_installed' => 'The :extension/:name extension is already installed.',
        'extension_not_installed' => 'The :extension/:name extension is not installed.',
        'extension_folder_not_found' => 'The :extension/:name extension folder not found.',
        'extension_runner_not_found' => 'The :extension/:name extension runner not found.',
        'extension_config_file_not_found' => 'The :extension/:name extension config file not found.',
        'extension_configuration_not_match' => 'The :extension/:name extension configuration not match.',
        'extension_class_name_not_match' => 'The :extension/:name extension class name not match.',
        'extension_dont_have_contract' => 'The :extension/:name extension dont have JobMetric\Extension\Contracts\ExtensionContract contract.',
        'extension_have_some_plugin' => 'The :extension extension have some plugin. Please uninstall the plugin first.',
        'plugin_not_found' => 'The plugin with ID :plugin_id not found.',
    ],

    'messages' => [
        'extension' => [
            'installed' => 'The extension has been installed.',
            'uninstalled' => 'The extension has been uninstalled.',
            'updated' => 'The extension has been updated.',
        ],

        'plugin' => [
            'added' => 'The plugin has been added.',
            'edited' => 'The plugin has been edited.',
            'deleted' => 'The plugin has been deleted.',
        ],
    ],

];
