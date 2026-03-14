<?php

namespace JobMetric\Extension\Tests\Feature;

use JobMetric\Extension\Kernel\ExtensionKernel;
use JobMetric\Extension\Services\Extension;
use JobMetric\Extension\Services\Plugin;
use JobMetric\Extension\Support\ExtensionNamespaceRegistry;
use JobMetric\Extension\Support\ExtensionRegistry;
use JobMetric\Extension\Support\ExtensionTypeRegistry;
use JobMetric\Extension\Support\InstalledExtensionsFile;
use JobMetric\Extension\Tests\TestCase;

/**
 * Feature tests for ExtensionServiceProvider bindings (all resolved within package).
 */
class ExtensionServiceProviderTest extends TestCase
{
    /**
     * ExtensionKernel is resolvable from container.
     */
    public function test_extension_kernel_is_resolvable(): void
    {
        $kernel = $this->app->make(ExtensionKernel::class);
        $this->assertInstanceOf(ExtensionKernel::class, $kernel);
    }

    /**
     * Extension service is resolvable from container.
     */
    public function test_extension_service_is_resolvable(): void
    {
        $this->assertInstanceOf(Extension::class, $this->app->make(Extension::class));
    }

    /**
     * Plugin service is resolvable from container.
     */
    public function test_plugin_service_is_resolvable(): void
    {
        $this->assertInstanceOf(Plugin::class, $this->app->make(Plugin::class));
    }

    /**
     * ExtensionNamespaceRegistry is resolvable from container.
     */
    public function test_extension_namespace_registry_is_resolvable(): void
    {
        $this->assertInstanceOf(ExtensionNamespaceRegistry::class, $this->app->make(ExtensionNamespaceRegistry::class));
    }

    /**
     * ExtensionTypeRegistry is resolvable from container.
     */
    public function test_extension_type_registry_is_resolvable(): void
    {
        $this->assertInstanceOf(ExtensionTypeRegistry::class, $this->app->make(ExtensionTypeRegistry::class));
    }

    /**
     * ExtensionRegistry is resolvable from container.
     */
    public function test_extension_registry_is_resolvable(): void
    {
        $this->assertInstanceOf(ExtensionRegistry::class, $this->app->make(ExtensionRegistry::class));
    }

    /**
     * InstalledExtensionsFile is resolvable from container.
     */
    public function test_installed_extensions_file_is_resolvable(): void
    {
        $this->assertInstanceOf(InstalledExtensionsFile::class, $this->app->make(InstalledExtensionsFile::class));
    }
}
