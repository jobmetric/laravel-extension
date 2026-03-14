<?php

namespace JobMetric\Extension\Tests\Unit\Kernel;

use JobMetric\Extension\Exceptions\ExtensionCoreNameRequiredException;
use JobMetric\Extension\Kernel\EventTrait;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for EventTrait lifecycle hooks (all callable without throwing).
 */
class EventTraitTest extends TestCase
{
    use EventTrait;

    /**
     * beforeRegisterExtension is callable without throwing.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_beforeRegisterExtension_is_callable(): void
    {
        $core = (new ExtensionCore)->name('Module_Banner');
        $this->beforeRegisterExtension($core, $this->app);
        $this->addToAssertionCount(1);
    }

    /**
     * afterRegisterExtension is callable without throwing.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_afterRegisterExtension_is_callable(): void
    {
        $core = (new ExtensionCore)->name('Module_Banner');
        $this->afterRegisterExtension($core, $this->app);
        $this->addToAssertionCount(1);
    }

    /**
     * configLoadedExtension is callable without throwing.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_configLoadedExtension_is_callable(): void
    {
        $core = (new ExtensionCore)->name('Module_Banner');
        $this->configLoadedExtension($core, $this->app);
        $this->addToAssertionCount(1);
    }

    /**
     * afterRegisterClassesExtension is callable without throwing.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_afterRegisterClassesExtension_is_callable(): void
    {
        $core = (new ExtensionCore)->name('Module_Banner');
        $this->afterRegisterClassesExtension($core, $this->app);
        $this->addToAssertionCount(1);
    }

    /**
     * beforeBootExtension is callable without throwing.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_beforeBootExtension_is_callable(): void
    {
        $core = (new ExtensionCore)->name('Module_Banner');
        $this->beforeBootExtension($core, $this->app);
        $this->addToAssertionCount(1);
    }

    /**
     * afterBootExtension is callable without throwing.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_afterBootExtension_is_callable(): void
    {
        $core = (new ExtensionCore)->name('Module_Banner');
        $this->afterBootExtension($core, $this->app);
        $this->addToAssertionCount(1);
    }
}
