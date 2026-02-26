<?php

namespace JobMetric\Extension\Kernel;

use Illuminate\Contracts\Foundation\Application;

/**
 * Lifecycle hooks for extension register and boot. Used by AbstractExtension; invoked by ExtensionCoreBooter
 * before/after each step. Override in concrete extensions to run custom logic.
 *
 * Register order: beforeRegisterExtension → (registerConfig → configLoadedExtension) → (registerClasses →
 * afterRegisterClassesExtension) → (registerViews → viewLoadedExtension) → (registerConsoleKernel →
 * consoleKernelLoadedExtension) → afterRegisterExtension.
 *
 * Boot order: beforeBootExtension → (bootTranslations → translationsLoadedExtension) → (bootRoutes →
 * routeLoadedExtension) → (bootComponents → componentLoadedExtension) → [with provider: afterRegisterCommandsExtension,
 * afterRegisterPublishableExtension] → afterBootExtension.
 *
 * @package JobMetric\Extension
 */
trait EventTrait
{
    /**
     * Fired once at the start of the register phase, before config/classes/views/ConsoleKernel.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function beforeRegisterExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired once at the end of the register phase, after all register steps.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function afterRegisterExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after config has been merged into the application (extension_{type}_{name}).
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function configLoadedExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after container bindings (bind/singleton/scoped/register) have been applied.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function afterRegisterClassesExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after the extension view namespace has been added (configKey => basePath/resources/views).
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function viewLoadedExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after the extension ConsoleKernel has been registered for booted callback (scheduling).
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function consoleKernelLoadedExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired once at the start of the boot phase, before translations/routes/components (and commands/publishables).
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function beforeBootExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired once at the end of the boot phase, after all boot steps.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function afterBootExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after translations have been loaded from basePath/lang under the extension config key.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function translationsLoadedExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after the extension route file (routes/route.php) has been registered in a booted callback.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function routeLoadedExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after the Blade component namespace (extension namespace\View\Components) has been registered.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function componentLoadedExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after extension Artisan commands have been registered on the provider (when boot is called with a provider).
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function afterRegisterCommandsExtension(ExtensionCore $core, Application $app): void
    {
    }

    /**
     * Fired after publishable paths (config, views, assets, custom, dependency) have been registered on the provider.
     *
     * @param ExtensionCore $core
     * @param Application $app
     *
     * @return void
     */
    public function afterRegisterPublishableExtension(ExtensionCore $core, Application $app): void
    {
    }
}
