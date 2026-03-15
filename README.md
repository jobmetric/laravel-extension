[contributors-shield]: https://img.shields.io/github/contributors/jobmetric/laravel-extension.svg?style=for-the-badge
[contributors-url]: https://github.com/jobmetric/laravel-extension/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jobmetric/laravel-extension.svg?style=for-the-badge&label=Fork
[forks-url]: https://github.com/jobmetric/laravel-extension/network/members
[stars-shield]: https://img.shields.io/github/stars/jobmetric/laravel-extension.svg?style=for-the-badge
[stars-url]: https://github.com/jobmetric/laravel-extension/stargazers
[license-shield]: https://img.shields.io/github/license/jobmetric/laravel-extension.svg?style=for-the-badge
[license-url]: https://github.com/jobmetric/laravel-extension/blob/master/LICENCE.md
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-blue.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/majidmohammadian

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

# Laravel Extension

**Build Extensions and Plugins. Scale Your App.**

Laravel Extension simplifies the management of extensions and plugins within Laravel applications. Stop wiring every feature by hand and start building installable, type-based extensions (e.g. Module, PaymentMethod) with their own migrations, config, routes, and plugins. It offers discovery from registered namespaces, install/uninstall lifecycle with migrations, plugin CRUD with form definitions from each extension, and a kernel that registers and boots only installed extensions. This is where powerful extension management meets developer-friendly simplicity—giving you complete control over extensions and plugins without the complexity.

## Why Laravel Extension?

### Type-Based Extensions

Create extensions by type (e.g. Module, ShippingMethod). Each type is registered in ExtensionTypeRegistry (via config or at runtime). Extensions live under a namespace registered in ExtensionNamespaceRegistry (e.g. `App\Extensions`). The kernel discovers extension classes from those namespaces and registers them by type. You get a clear structure: one extension class per type+name, with `extension.json`, migrations, and optional views/translations.

### Install and Uninstall Lifecycle

Install an extension by namespace: the package runs its migrations, stores the extension record, and when the extension is not "multiple", creates a default plugin. Uninstall rolls back migrations, deletes plugins, and removes the extension record. InstalledExtensionsFile keeps a JSON cache of installed namespaces so the kernel can load only installed extensions. Upgrade runs new migrations when the extension version increases; you can also delete extension files from disk after uninstall when they live under `App\Extensions`.

### Plugins with Form-Driven Data

Each extension extends AbstractExtension and defines `form()` (FormBuilder) and `handle(array $options)`. Plugins belong to an extension and store name, fields (form output), and status. The Plugin service uses StorePluginRequest/UpdatePluginRequest and the extension’s form to validate and normalize plugin data. You can list plugins, add or edit them per extension, and run extension logic via `handle()` with the plugin’s stored options.

### Kernel: Discover, Register, Boot

ExtensionKernel discovers extension classes from ExtensionNamespaceRegistry, registers them in ExtensionRegistry (type => namespaces). It loads only installed extensions from the database, then runs register (configuration, bindings) and boot (migrations, routes, views, translations) per extension in priority order. Route model bindings for `jm_extension` and `jm_plugin` are registered; upgrade and boot run after the app is booted. Optional discover cache (config) avoids filesystem scans on every request.

## What is Extension Management?

Extension management is the process of defining, discovering, installing, and running extensions and their plugins. In a traditional Laravel application, you might add features by hand in the same codebase with no install/uninstall story. Laravel Extension takes a different approach:

- **Discovery**: ExtensionNamespaceRegistry holds namespaces (e.g. `App\Extensions`); ExtensionKernel scans them and registers each extension class in ExtensionRegistry by type (from extension.json or class).
- **Types**: ExtensionTypeRegistry holds extension types (e.g. Module) with optional label/description; config `extension.types` seeds it; you can register more at runtime.
- **Install/Uninstall**: Extension service installs by FQCN (runs migrations, stores record, optional default plugin), uninstalls (rollback, delete plugins, destroy record), and can delete extension files from disk when uninstalled and under App\Extensions.
- **Plugins**: Each extension can have one or many plugins (when `multiple` in extension.json); Plugin service provides store/update/destroy and storeForExtension/updateForExtension; form and options come from AbstractExtension::form() and handle().

Consider a "Banner" module: you create the extension class under `App\Extensions\Module\Banner`, add `extension.json` (extension, name, version, title, multiple, etc.), add a `migrations` folder with versioned migration files, implement `configuration(ExtensionCore)`, `form()`, and `handle(options)`. After running `php artisan migrate`, you install it via Extension::install(namespace); the kernel then registers and boots it. You add plugins via Plugin::store(extensionId, data); each plugin’s fields are validated by the extension’s form. The power of extension management lies in having a single lifecycle: discover types and classes, install only what you need, and run each extension’s logic through plugins with structured data.

## What Awaits You?

By adopting Laravel Extension, you will:

- **Structure extensions by type** - Module, PaymentMethod, or custom types; each extension has one class, extension.json, and optional migrations and assets
- **Install and uninstall cleanly** - Migrations run on install and roll back on uninstall; optional default plugin; delete files when under App\Extensions
- **Manage plugins per extension** - Store and update plugins with form-based validation; run extension logic via handle(options)
- **Control discovery and boot** - Registries for namespaces and types; kernel discovers, loads installed only, then registers and boots in priority order
- **Use Artisan commands** - extension:make, extension:make-tools, extension:install, extension:uninstall to generate and manage extensions
- **Integrate with the rest of the stack** - Laravel Layout can position plugins; events cover install, uninstall, delete, and plugin CRUD; facades and helpers (extension_install, extension_uninstall) for easy access

## Quick Start

Install Laravel Extension via Composer:

```bash
composer require jobmetric/laravel-extension
```

## Documentation

Ready to transform your Laravel applications? Our comprehensive documentation is your gateway to mastering Laravel Extension:

**[📚 Read Full Documentation →](https://jobmetric.github.io/packages/laravel-extension/)**

The documentation includes:

- **Getting Started** - Quick introduction, installation, and migrate after install
- **Extension Service** - install, uninstall, delete (files), list by type (installed/needs_update), getInfo, upgrade, isUpdated, namespaceFor
- **Plugin Service** - store, update, destroy, storeForExtension, updateForExtension; form validation via AbstractExtension::form()
- **AbstractExtension** - configuration(ExtensionCore), form(), handle(options); extension.json (extension, name, version, title, multiple, priority, depends); install/uninstall migrations
- **ExtensionKernel** - discover, loadInstalledExtensions, registerExtensions, bootExtensions, upgradeExtensions
- **Registries** - ExtensionNamespaceRegistry, ExtensionTypeRegistry, ExtensionRegistry; InstalledExtensionsFile
- **Commands** - extension:make, extension:make-tools, extension:install, extension:uninstall
- **Models & Resources** - Extension, Plugin, ExtensionMigration; ExtensionResource, PluginResource
- **Requests** - StorePluginRequest, UpdatePluginRequest (form-based)
- **Events** - Extension install/uninstall/delete; Plugin store/update/delete; kernel discovering/registered/booted
- **Helpers** - extension_install(type, name), extension_uninstall(type, name, force_delete_plugin)
- **Real-World Examples** - See how it works in practice

## Contributing

Thank you for participating in `laravel-extension`. A contribution guide can be found [here](CONTRIBUTING.md).

## License

The `laravel-extension` is open-sourced software licensed under the MIT license. See [License File](LICENCE.md) for more information.
