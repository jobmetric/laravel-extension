<?php

namespace JobMetric\Extension\Kernel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreNameRequiredException;
use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;

/**
 * Extension register/boot flow (like PackageCoreServiceProvider). Runs steps and calls EventTrait hooks on the
 * extension before/after each step; hooks are defined in EventTrait, extension receives them via AbstractExtension.
 *
 * @package JobMetric\Extension
 */
class ExtensionCoreBooter
{
    /**
     * Run register phase: config, class bindings, view namespace, ConsoleKernel. After each step calls the
     * corresponding hook on the extension (EventTrait).
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param AbstractExtension|null $extension
     *
     * @return void
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public static function register(ExtensionCore $core, Application $app, ?AbstractExtension $extension = null): void
    {
        $configKey = $core->getConfigKey();

        $extension?->beforeRegisterExtension($core, $app);

        self::registerConfig($core, $app, $configKey, $extension);
        self::registerClasses($core, $app, $extension);
        self::registerViews($core, $app, $configKey, $extension);
        self::registerConsoleKernel($core, $app, $extension);

        $extension?->afterRegisterExtension($core, $app);
    }

    /**
     * Run boot phase: translations, routes, components; with provider: commands and publishables. After each step
     * calls the corresponding hook on the extension (EventTrait).
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param ServiceProvider|null $provider
     * @param AbstractExtension|null $extension
     * @param callable(array, string|array): void|null $publishCallback Called to register publishable paths; receives
     *                                       (paths, groups). Pass e.g. fn($p, $g) => $this->publishes($p, $g) from the
     *                                       provider so publishes() runs in provider context (it is protected).
     *
     * @return void
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public static function boot(
        ExtensionCore $core,
        Application $app,
        ?ServiceProvider $provider = null,
        ?AbstractExtension $extension = null,
        ?callable $publishCallback = null
    ): void {
        $basePath = $core->getBasePath();
        $configKey = $core->getConfigKey();

        $extension?->beforeBootExtension($core, $app);

        self::bootTranslations($core, $basePath, $configKey, $app, $extension);
        self::bootRoutes($core, $basePath, $app, $extension);
        self::bootComponents($core, $configKey, $app, $extension);

        if ($provider !== null) {
            self::bootProviderCommands($core, $app, $provider, $extension);
            self::bootProviderPublishables($core, $basePath, $app, $extension, $publishCallback);
        }

        $extension?->afterBootExtension($core, $app);
    }

    /**
     * Add extension views as view namespace (in register phase, like PackageCore loadView). Calls viewLoadedExtension
     * when done.
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param string $configKey
     * @param AbstractExtension|null $extension
     *
     * @return void
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathRequiredException
     */
    public static function registerViews(
        ExtensionCore $core,
        Application $app,
        string $configKey,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['hasView'])) {
            return;
        }

        $app->make('view')
            ->addNamespace($configKey, $core->getBasePath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views');

        $extension?->viewLoadedExtension($core, $app);
    }

    /**
     * Register ConsoleKernel to be resolved in booted callback (in register phase, like PackageCore
     * loadConsoleKernel). Calls consoleKernelLoadedExtension when done.
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param AbstractExtension|null $extension
     *
     * @return void
     * @throws ExtensionCoreBasePathRequiredException
     */
    public static function registerConsoleKernel(
        ExtensionCore $core,
        Application $app,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['hasConsoleKernel'])) {
            return;
        }

        $namespace = $core->getNamespace();
        if ($namespace === null) {
            return;
        }

        $consoleKernelClass = $namespace . '\\ConsoleKernel';
        if (! class_exists($consoleKernelClass)) {
            return;
        }

        $app->booted(function () use ($app, $consoleKernelClass) {
            $app->make($consoleKernelClass);
        });

        $extension?->consoleKernelLoadedExtension($core, $app);
    }

    /**
     * Merge extension config/config.php into app config under extension_{type}_{name} (e.g. extension_module_banner). Calls
     * configLoadedExtension when done.
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param string $configKey
     * @param AbstractExtension|null $extension
     *
     * @return void
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathRequiredException
     */
    public static function registerConfig(
        ExtensionCore $core,
        Application $app,
        string $configKey,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['hasConfig'])) {
            return;
        }

        $config = $app->make('config');
        $config->set($configKey, array_merge(require $core->getBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php', $config->get($configKey, [])));

        $extension?->configLoadedExtension($core, $app);
    }

    /**
     * Bind extension-registered classes into container (bind, singleton, scoped, register). Calls
     * afterRegisterClassesExtension when done.
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param AbstractExtension|null $extension
     *
     * @return void
     */
    public static function registerClasses(
        ExtensionCore $core,
        Application $app,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['classes'])) {
            return;
        }

        foreach ($core->option['classes'] as $key => $item) {
            $classOrFactory = $item['class'];
            $type = $item['type'];

            if ($type === RegisterClassTypeEnum::BIND()) {
                $app->bind($key, $classOrFactory);
            }

            if ($type === RegisterClassTypeEnum::SINGLETON()) {
                $app->singleton($key, $classOrFactory);
            }

            if ($type === RegisterClassTypeEnum::SCOPED()) {
                $app->scoped($key, $classOrFactory);
            }

            if ($type === RegisterClassTypeEnum::REGISTER()) {
                $app->register($classOrFactory);
            }
        }

        $extension?->afterRegisterClassesExtension($core, $app);
    }

    /**
     * Load basePath/routes/route.php in a booted callback. Path validated in ExtensionCore::hasRoute(). Calls
     * routeLoadedExtension when done.
     *
     * @param ExtensionCore $core
     * @param string $basePath
     * @param Application $app
     * @param AbstractExtension|null $extension
     *
     * @return void
     */
    public static function bootRoutes(
        ExtensionCore $core,
        string $basePath,
        Application $app,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['hasRoute'])) {
            return;
        }

        $app->booted(function () use ($basePath) {
            require $basePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'route.php';
        });

        $extension?->routeLoadedExtension($core, $app);
    }

    /**
     * Load translations from basePath/lang. Path validated in ExtensionCore::hasTranslation(). Calls
     * translationsLoadedExtension when done.
     *
     * @param ExtensionCore $core
     * @param string $basePath
     * @param string $configKey
     * @param Application $app
     * @param AbstractExtension|null $extension
     *
     * @return void
     */
    public static function bootTranslations(
        ExtensionCore $core,
        string $basePath,
        string $configKey,
        Application $app,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['hasTranslation'])) {
            return;
        }

        $app->loadTranslationsFrom($basePath . DIRECTORY_SEPARATOR . 'lang', $configKey);

        $extension?->translationsLoadedExtension($core, $app);
    }

    /**
     * Register Blade component namespace (extension namespace\View\Components). Folder validated in
     * ExtensionCore::hasComponent(). Calls componentLoadedExtension when done.
     *
     * @param ExtensionCore $core
     * @param string $configKey
     * @param Application $app
     * @param AbstractExtension|null $extension
     *
     * @return void
     * @throws ExtensionCoreBasePathRequiredException
     */
    public static function bootComponents(
        ExtensionCore $core,
        string $configKey,
        Application $app,
        ?AbstractExtension $extension = null
    ): void {
        if (! isset($core->option['hasComponent'])) {
            return;
        }

        $namespace = $core->getNamespace();

        if ($namespace !== null) {
            Blade::componentNamespace($namespace . '\\View\\Components', $configKey);
        }

        $extension?->componentLoadedExtension($core, $app);
    }

    /**
     * Register extension commands on provider so they appear in artisan list. Calls afterRegisterCommandsExtension
     * when done.
     *
     * @param ExtensionCore $core
     * @param Application $app
     * @param ServiceProvider $provider Concrete ServiceProvider (has commands()); do not type as
     *                                                      contract.
     * @param AbstractExtension|null $extension
     *
     * @return void
     */
    public static function bootProviderCommands(
        ExtensionCore $core,
        Application $app,
        ServiceProvider $provider,
        ?AbstractExtension $extension = null
    ): void {
        if (empty($core->option['commands'])) {
            return;
        }

        $provider->commands($core->option['commands']);

        $extension?->afterRegisterCommandsExtension($core, $app);
    }

    /**
     * Register publishables via callback (publishes() is protected on ServiceProvider). Callback receives (paths,
     * groups).
     *
     * @param ExtensionCore $core
     * @param string $basePath
     * @param Application $app
     * @param AbstractExtension|null $extension
     * @param callable(array, string|array): void|null $publishCallback e.g. fn($paths, $groups) =>
     *                                       $this->publishes($paths, $groups) from the provider.
     *
     * @return void
     * @throws ExtensionCoreNameRequiredException
     */
    public static function bootProviderPublishables(
        ExtensionCore $core,
        string $basePath,
        Application $app,
        ?AbstractExtension $extension = null,
        ?callable $publishCallback = null
    ): void {
        if ($publishCallback === null) {
            $extension?->afterRegisterPublishableExtension($core, $app);

            return;
        }

        if (! empty($core->option['publishable'])) {
            foreach ($core->option['publishable'] as $item) {
                $publishCallback($item['paths'], $item['groups']);
            }
        }

        if (! empty($core->option['dependency_publishable'])) {
            foreach ($core->option['dependency_publishable'] as $item) {
                $depProvider = $item['provider'];
                $group = $item['group'];
                if (method_exists($depProvider, 'pathsToPublish')) {
                    $publishables = $depProvider::pathsToPublish(null, $group);
                    $publishCallback($publishables, [$core->name, $core->name . '-dependency']);
                }
            }
        }

        if (! empty($core->option['isPublishableView']) && isset($core->option['hasView'])) {
            $viewPath = $basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
            $publishCallback([
                $viewPath => resource_path('views' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $core->shortName()),
            ], [$core->name, $core->name . '-views']);
        }

        if (! empty($core->option['hasAsset'])) {
            $assetPath = $basePath . DIRECTORY_SEPARATOR . 'assets';
            $publishCallback([
                $assetPath => public_path('assets' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $core->shortName()),
            ], [$core->name, $core->name . '-assets']);
        }

        $extension?->afterRegisterPublishableExtension($core, $app);
    }
}
