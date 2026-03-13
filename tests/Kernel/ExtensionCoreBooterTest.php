<?php

namespace JobMetric\Extension\Tests\Kernel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreClassNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreNameRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreRegisterClassTypeNotFoundException;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Kernel\ExtensionCoreBooter;
use JobMetric\Extension\Tests\TestCase;
use stdClass;

/**
 * Tests for ExtensionCoreBooter.
 */
class ExtensionCoreBooterTest extends TestCase
{
    /**
     * register with core that has no options does not throw.
     *
     * @throws ExtensionCoreNameRequiredException
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     */
    public function test_register_with_minimal_core(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::register($core, $this->app, null);

        $this->assertTrue(true);
    }

    /**
     * registerViews when core has no hasView returns without adding namespace.
     *
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_registerViews_skips_when_no_hasView(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::registerViews($core, $this->app, 'extension_test', null);

        $this->assertTrue(true);
    }

    /**
     * registerConfig when core has no hasConfig returns without loading.
     *
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_registerConfig_skips_when_no_hasConfig(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::registerConfig($core, $this->app, 'extension_test', null);

        $this->assertTrue(true);
    }

    /**
     * registerClasses when core has no classes returns without binding.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_registerClasses_skips_when_no_classes(): void
    {
        $core = new ExtensionCore;
        $core->name('Test');

        ExtensionCoreBooter::registerClasses($core, $this->app, null);

        $this->assertTrue(true);
    }

    /**
     * registerClasses binds class when core has classes option with bind type.
     *
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreClassNotFoundException
     * @throws ExtensionCoreRegisterClassTypeNotFoundException
     */
    public function test_registerClasses_binds_when_classes_set(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->registerClass('test.ext.core.key', stdClass::class, 'bind');

        ExtensionCoreBooter::registerClasses($core, $this->app, null);

        $this->assertTrue($this->app->bound('test.ext.core.key'));
        $this->assertInstanceOf(stdClass::class, $this->app->make('test.ext.core.key'));
    }

    /**
     * registerConsoleKernel when core has no hasConsoleKernel returns.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_registerConsoleKernel_skips_when_not_set(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::registerConsoleKernel($core, $this->app, null);

        $this->assertTrue(true);
    }

    /**
     * boot with minimal core does not throw.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_boot_with_minimal_core(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::boot($core, $this->app, null, null, null);

        $this->assertTrue(true);
    }

    /**
     * bootRoutes when core has no hasRoute does not require file.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_bootRoutes_skips_when_no_hasRoute(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::bootRoutes($core, sys_get_temp_dir(), $this->app, null);

        $this->assertTrue(true);
    }

    /**
     * bootTranslations when core has no hasTranslation returns.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_bootTranslations_skips_when_no_hasTranslation(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::bootTranslations($core, sys_get_temp_dir(), 'extension_test', $this->app, null);

        $this->assertTrue(true);
    }

    /**
     * bootComponents when core has no hasComponent returns.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_bootComponents_skips_when_no_hasComponent(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::bootComponents($core, 'extension_test', $this->app, null);

        $this->assertTrue(true);
    }

    /**
     * bootProviderCommands when core has no commands returns.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_bootProviderCommands_skips_when_no_commands(): void
    {
        $provider = new class($this->app) extends ServiceProvider
        {
            public function register(): void
            {
            }

            public function boot(): void
            {
            }
        };

        $core = new ExtensionCore;
        $core->name('Test');

        ExtensionCoreBooter::bootProviderCommands($core, $this->app, $provider, null);

        $this->assertTrue(true);
    }

    /**
     * bootProviderPublishables with null callback calls extension hook and returns.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_bootProviderPublishables_with_null_callback(): void
    {
        $core = new ExtensionCore;
        $core->name('Test')->setBasePath(sys_get_temp_dir());

        ExtensionCoreBooter::bootProviderPublishables($core, sys_get_temp_dir(), $this->app, null, null);

        $this->assertTrue(true);
    }
}
