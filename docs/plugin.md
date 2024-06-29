[Back To README.md](https://github.com/jobmetric/laravel-extension/blob/master/README.md)

# Introduction to Plugin

Well, it's time to define the plugin document.

One of the most important parts of this package that makes your beloved programs run here and be available to the main program.

In this section, we will explain how to create a plugin and how to run it in the main program.

### What is a plugin?

The plugin is a mini program created in the main program after installing the extension and can be run in the main program.

> If the feature of the `multiple` field value in the extension.json file is equal to `true`, a large number of extensions can be created that can have different fields.

## How to create a plugin?

When the `extension` is installed, you can use the following command to add the plugin to the system with the features of the same extension.

```php
\JobMetric\Extension\Facades\Plugin::add('Module', 'Banner', $fields);
```

You must first capture the `fields` of that extension using the following function and then send the information.

```php
\JobMetric\Extension\Facades\Extension::fields('Module', 'Banner', $plugin_id);
```

> `plugin_id` is the plugin id which you can get from the plugin list. And it is used when updating the plugin.

## Hooray, now your plugin is installed and ready to run in the main program.

Now you can run your program using the run method.

```php
$plugin = \JobMetric\Extension\Facades\Plugin::run($plugin_id);
```

> If the desired plugin has no output, the `null` value will be returned.
