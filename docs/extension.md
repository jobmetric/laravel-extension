[Back To README.md](https://github.com/jobmetric/laravel-extension/blob/master/README.md)

# Introduction to Extension

### How to create an extension

To create an extension, you can use the following command and implement a simple extension

```bash
php artisan extension:make module/banner -fm
```

With this command, you created an extension in the `app/extension` folder called `Module/Banner`, which contains the following files:

```bash
├── app
│   └── Extensions
│       └── Module
│           └── Banner
│               └── Lang
│               │   └── en
│               │       └── extension.php
│               ├── Banner.php
│               ├── extension.json
```

> The `-f` flag is used to create a full extension
>
> The `-m` flag is used to create an extension with multiple plugins

The first folder after the extension, which is called Module in this example, is for the type of extension that we are going to create, which is selected from different types of data that are in the `JobMetric\Extension\Enums\ExtensionTypeEnum`.

The second folder is the name of the extension that you want to use, and it is a kind of `username` for your extension that we can make famous and sell in the market.

#### extension.json

The created package has an extension.json file that contains all the extension settings as shown below.

```json
{
    "extension": "Module",
    "name": "Banner",
    "version": "1.0.0",
    "multiple": true,
    "title": "Banner Module",
    "description": "This is a banner module",
    "author": "Majid Mohammadian",
    "email": "majeedmohammadian@gmail.com",
    "website": "https://jobmetric.net",
    "creationDate": "2024-06-27 18:43:17",
    "copyright": "Copyright (c) 2021",
    "license": "MIT",
    "fields": [
        {
            "name": "width",
            "type": "number",
            "required": true,
            "default": 100,
            "label": "Width",
            "info": "The width of the banner",
            "placeholder": "Enter the width of the banner",
            "validation": "numeric|min:1|max:1000"
        },
        {
            "name": "height",
            "type": "number",
            "required": true,
            "default": 100,
            "label": "Height",
            "info": "The height of the banner",
            "placeholder": "Enter the height of the banner",
            "validation": "numeric|min:1|max:1000"
        }
    ]
}
```

> `extension` is the type of extension
>
> `name` is the name of the extension
>
> `version` is the version of the extension
>
> `multiple` is the type of extension that can have multiple plugins
>
> `title` is the title of the extension
>
> `description` is the description of the extension
>
> `author` is the author of the extension
>
> `email` is the email of the author
>
> `website` is the website of the author
>
> `creationDate` is the creation date of the extension
>
> `copyright` is the copyright
>
> `license` is the license of the extension
>
> `Fields` are extension program fields, the different types of which you can read from [this document](https://github.com/jobmetric/laravel-extension/blob/master/docs/fields.md)

#### language files

The language files are stored in the `Lang` folder, which contains the language files for the extension.

To use language keys in your program, you can code as follows:

```php
__('extension_Module_Banner::extension.key')
```

> `extension_Module_Banner::extension` is the name of the language file
>
> `key` is the key of the language file

#### Where should I write the extension code?

The extension code is written in the `Banner.php` file, which is in the plugin folder, in the `handle` method

```php
<?php

namespace App\Extensions\Module\Banner;

use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Form\FormBuilder;

class Banner extends AbstractExtension
{
    public static function extension(): string { return 'Module'; }
    public static function name(): string { return 'Banner'; }
    public static function version(): string { return '1.0.0'; }
    public static function title(): string { return 'Banner'; }
    public static function multiple(): bool { return false; }

    public function form(): FormBuilder
    {
        return new FormBuilder();
    }

    public function handle(array $options = []): ?string
    {
        return null;
    }
}
```

> The values of $options that are sent as an array in the input are the values of the filled fields each plugin that are sent to the plugin.

#### Installations

After creating the extension, you must install it in the system using the following command

```bash
php artisan extension:install module/banner
```

Or you can use the installation method in the following facade class in your program

```php
\JobMetric\Extension\Facades\Extension::install('Module', 'Banner');
```

With this, you have registered your extension in the extension's table, and now it is ready to add the plugin, which you should explore in the [plugin documentation](https://github.com/jobmetric/laravel-extension/blob/master/docs/plugin.md).

After the extension is installed by the above commands, a method called `install` is called inside Banner.php, which you can complete the codes inside and put the changes you want to make inside it.

```php
/**
 * Install the extension.
 *
 * @return void
 */
public static function install(): void
{
    //
}
```

> If you have used the `-f` flag in the build, this method is available inside your extension and there is no need to rewrite it.

#### Uninstallations

If the desired extension is already installed, delete it in the system using the following command.

```bash
php artisan extension:uninstall module/banner
```

Or you can use the uninstallation method in the following facade class in your program

```php
\JobMetric\Extension\Facades\Extension::uninstall('Module', 'Banner');
```

After the extension is uninstalled by the above commands, a method called `uninstall` is called inside Banner.php, which you can complete the codes inside and put the changes you want to make inside it.

```php
/**
 * Uninstall the extension.
 *
 * @return void
 */
public static function uninstall(): void
{
    //
}
```

> If you have used the `-f` flag in the build, this method is available inside your extension and there is no need to rewrite it.


- [Next To Fields Extension](https://github.com/jobmetric/laravel-extension/blob/master/docs/fields.md)
