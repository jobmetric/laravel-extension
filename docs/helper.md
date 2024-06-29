[Back To README.md](https://github.com/jobmetric/laravel-extension/blob/master/README.md)

# Helper Functions

In this section, we will explain the helper functions that are used in the extension package.

### extension_install

This function is used to install the extension in the system.

```php
extension_install(string $extension, string $name);
```

> The `extension` value is the name of the extension that you have defined in the `extension.json` file.
> 
> The `name` value is the name of the extension that you want to install in the system.

### extension_uninstall

This function is used to uninstall the extension in the system.

```php
extension_uninstall(string $extension, string $name, bool $force_delete_plugin = false);
```

> The `extension` value is the name of the extension that you have defined in the `extension.json` file.
> 
> The `name` value is the name of the extension that you want to uninstall in the system.
> 
> The `force_delete_plugin` value is used to force the deletion of the plugin in the system.

### extension_update

This function is used to update the extension in the system.

```php
extension_update(string $extension, string $name);
```

> The `extension` value is the name of the extension that you have defined in the `extension.json` file.
> 
> The `name` value is the name of the extension that you want to update in the system.

### plugin_add

This function is used to add the plugin in the system.

```php
plugin_add(string $extension, string $name, array $fields);
```

> The `extension` value is the name of the extension that you have defined in the `extension.json` file.
> 
> The `name` value is the name of the extension that you want to add in the system.
> 
> The `fields` value is the fields of the extension that you want to add in the system.

### plugin_edit

This function is used to edit the plugin in the system.

```php
plugin_edit(int $plugin_id, array $fields);
```

> The `plugin_id` value is the id of the plugin that you want to edit in the system.
> 
> The `fields` value is the fields of the extension that you want to edit in the system.

### plugin_delete

This function is used to delete the plugin in the system.

```php
plugin_delete(int $plugin_id);
```

> The `plugin_id` value is the id of the plugin that you want to delete in the system.

### plugin_run

This function is used to run the plugin in the system.

```php
plugin_run(int $plugin_id);
```

> The `plugin_id` value is the id of the plugin that you want to run in the system.
