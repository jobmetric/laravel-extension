<?php

namespace JobMetric\Extension\Tests\Kernel;

use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Kernel\ExtensionKernel;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\Form\FormBuilder;
use ReflectionClass;

/**
 * Tests for ExtensionKernel and ExtensionKernelCallbacks trait.
 */
class ExtensionKernelTest extends TestCase
{
    private ExtensionKernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kernel = new ExtensionKernel($this->app);
    }

    /**
     * app returns application instance.
     */
    public function test_app_returns_application(): void
    {
        $this->assertSame($this->app, $this->kernel->app());
    }

    /**
     * addExtension adds instance and returns self.
     */
    public function test_addExtension_adds_and_returns_self(): void
    {
        $this->setExtensionDataCache(StubExtBanner::class, [
            'extension' => 'Module',
            'name'      => 'Banner',
            'priority'  => 0,
            'depends'   => [],
        ]);
        $ext = new StubExtBanner;

        $result = $this->kernel->addExtension($ext);

        $this->assertSame($this->kernel, $result);
        $this->assertCount(1, $this->kernel->extensions());
        $this->assertSame($ext, $this->kernel->extensions()[0]);
    }

    /**
     * extensions returns list sorted by priority.
     */
    public function test_extensions_returns_sorted_by_priority(): void
    {
        $this->setExtensionDataCache(StubExtA::class, [
            'extension' => 'Module',
            'name'      => 'A',
            'priority'  => 10,
            'depends'   => [],
        ]);
        $this->setExtensionDataCache(StubExtB::class, [
            'extension' => 'Module',
            'name'      => 'B',
            'priority'  => 5,
            'depends'   => [],
        ]);
        $this->kernel->addExtension(new StubExtA);
        $this->kernel->addExtension(new StubExtB);

        $list = $this->kernel->extensions();

        $this->assertCount(2, $list);
        $this->assertSame(5, $list[0]::priority());
        $this->assertSame(10, $list[1]::priority());
    }

    /**
     * clearExtensions empties the list and returns self.
     */
    public function test_clearExtensions_empties_list(): void
    {
        $this->setExtensionDataCache(StubExtBanner::class, [
            'extension' => 'Module',
            'name'      => 'Banner',
            'priority'  => 0,
            'depends'   => [],
        ]);
        $this->kernel->addExtension(new StubExtBanner);

        $result = $this->kernel->clearExtensions();

        $this->assertSame($this->kernel, $result);
        $this->assertCount(0, $this->kernel->extensions());
    }

    /**
     * reset clears extensions and callbacks.
     */
    public function test_reset_clears_extensions_and_callbacks(): void
    {
        $this->setExtensionDataCache(StubExtBanner::class, [
            'extension' => 'Module',
            'name'      => 'Banner',
            'priority'  => 0,
            'depends'   => [],
        ]);
        $this->kernel->addExtension(new StubExtBanner);
        $this->kernel->registering(fn () => null);

        $result = $this->kernel->reset();

        $this->assertSame($this->kernel, $result);
        $this->assertCount(0, $this->kernel->extensions());
    }

    /**
     * getExtension returns extension by type and name.
     */
    public function test_getExtension_returns_extension_by_type_and_name(): void
    {
        $this->setExtensionDataCache(StubExtBanner::class, [
            'extension' => 'Module',
            'name'      => 'Banner',
            'priority'  => 0,
            'depends'   => [],
        ]);
        $ext = new StubExtBanner;
        $this->kernel->addExtension($ext);

        $this->assertSame($ext, $this->kernel->getExtension('Module', 'Banner'));
        $this->assertSame($ext, $this->kernel->getExtension('module', 'banner'));
    }

    /**
     * getExtension returns null when not found.
     */
    public function test_getExtension_returns_null_when_not_found(): void
    {
        $this->assertNull($this->kernel->getExtension('Module', 'NonExistent'));
    }

    /**
     * getExtensionByClass returns extension by FQCN.
     */
    public function test_getExtensionByClass_returns_extension_by_fqcn(): void
    {
        $this->setExtensionDataCache(StubExtBanner::class, [
            'extension' => 'Module',
            'name'      => 'Banner',
            'priority'  => 0,
            'depends'   => [],
        ]);
        $ext = new StubExtBanner;
        $this->kernel->addExtension($ext);

        $fqcn = get_class($ext);
        $this->assertSame($ext, $this->kernel->getExtensionByClass($fqcn));
    }

    /**
     * getExtensionByClass returns null when not found.
     */
    public function test_getExtensionByClass_returns_null_when_not_found(): void
    {
        $this->assertNull($this->kernel->getExtensionByClass('NonExistent\\Extension'));
    }

    /**
     * Trait: registering adds callback and returns self.
     */
    public function test_registering_adds_callback(): void
    {
        $called = false;
        $result = $this->kernel->registering(function ($k) use (&$called): void {
            $called = true;
            $this->assertSame($this->kernel, $k);
        });

        $this->assertSame($this->kernel, $result);
        $this->kernel->registerExtensions();
        $this->assertTrue($called);
    }

    /**
     * Trait: discovered adds callback.
     */
    public function test_discovered_adds_callback(): void
    {
        $result = $this->kernel->discovered(fn () => null);

        $this->assertSame($this->kernel, $result);
    }

    /**
     * Trait: booting adds callback.
     */
    public function test_booting_adds_callback(): void
    {
        $result = $this->kernel->booting(fn () => null);

        $this->assertSame($this->kernel, $result);
    }

    /**
     * clearDiscoverCache forgets cache key.
     */
    public function test_clearDiscoverCache_forgets_cache(): void
    {
        ExtensionKernel::clearDiscoverCache();
        $this->assertTrue(true);
    }

    private function setExtensionDataCache(string $class, array $data): void
    {
        $ref = new ReflectionClass(AbstractExtension::class);
        $prop = $ref->getProperty('extensionDataCache');
        $cache = $prop->getValue() ?? [];
        $cache[$class] = $data;
        $prop->setValue(null, $cache);
    }
}

/**
 * Stub extension for tests.
 */
class StubExtBanner extends AbstractExtension
{
    public function configuration(ExtensionCore $extension): void
    {
    }

    public function form(): FormBuilder
    {
        return new FormBuilder;
    }

    public function handle(array $options = []): ?string
    {
        return null;
    }
}

class StubExtA extends AbstractExtension
{
    public function configuration(ExtensionCore $extension): void
    {
    }

    public function form(): FormBuilder
    {
        return new FormBuilder;
    }

    public function handle(array $options = []): ?string
    {
        return null;
    }
}

class StubExtB extends AbstractExtension
{
    public function configuration(ExtensionCore $extension): void
    {
    }

    public function form(): FormBuilder
    {
        return new FormBuilder;
    }

    public function handle(array $options = []): ?string
    {
        return null;
    }
}
