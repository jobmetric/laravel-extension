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
        'extension_folder_not_found' => 'The :extension/:name extension folder not found.',
        'extension_runner_not_found' => 'The :extension/:name extension runner not found.',
        'extension_config_file_not_found' => 'The :extension/:name extension config file not found.',
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
