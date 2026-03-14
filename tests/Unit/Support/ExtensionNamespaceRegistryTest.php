<?php

namespace JobMetric\Extension\Tests\Unit\Support;

use JobMetric\Extension\Support\ExtensionNamespaceRegistry;
use JobMetric\Extension\Tests\TestCase;

/**
 * Tests for ExtensionNamespaceRegistry.
 */
class ExtensionNamespaceRegistryTest extends TestCase
{
    /**
     * register adds a namespace and returns self for chaining.
     */
    public function test_register_adds_namespace_and_returns_self(): void
    {
        $registry = new ExtensionNamespaceRegistry;

        $result = $registry->register('App\\Extensions');

        $this->assertSame($registry, $result);
        $this->assertSame(['App\\Extensions'], $registry->all());
    }

    /**
     * register does not add the same namespace twice.
     */
    public function test_register_does_not_add_duplicate(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions')->register('App\\Extensions');

        $this->assertSame(['App\\Extensions'], $registry->all());
    }

    /**
     * register trims leading and trailing backslashes.
     */
    public function test_register_trims_backslashes(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('\\App\\Extensions\\');

        $this->assertTrue($registry->has('App\\Extensions'));
        $this->assertSame(['App\\Extensions'], $registry->all());
    }

    /**
     * register does not add empty string after trim.
     */
    public function test_register_ignores_empty_after_trim(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('\\');

        $this->assertSame([], $registry->all());
    }

    /**
     * unregister removes namespace and returns self.
     */
    public function test_unregister_removes_namespace_and_returns_self(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions')->register('Vendor\\Extensions');

        $result = $registry->unregister('App\\Extensions');

        $this->assertSame($registry, $result);
        $this->assertSame(['Vendor\\Extensions'], $registry->all());
    }

    /**
     * unregister trims namespace before lookup.
     */
    public function test_unregister_trims_namespace(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions');

        $registry->unregister('\\App\\Extensions\\');

        $this->assertFalse($registry->has('App\\Extensions'));
        $this->assertSame([], $registry->all());
    }

    /**
     * unregister on non-existent namespace leaves registry unchanged.
     */
    public function test_unregister_on_missing_namespace_does_nothing(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions');

        $registry->unregister('Vendor\\Other');

        $this->assertSame(['App\\Extensions'], $registry->all());
    }

    /**
     * has returns true when namespace is registered.
     */
    public function test_has_returns_true_when_registered(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions');

        $this->assertTrue($registry->has('App\\Extensions'));
    }

    /**
     * has returns false when namespace is not registered.
     */
    public function test_has_returns_false_when_not_registered(): void
    {
        $registry = new ExtensionNamespaceRegistry;

        $this->assertFalse($registry->has('App\\Extensions'));
    }

    /**
     * has trims namespace before check.
     */
    public function test_has_trims_namespace(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions');

        $this->assertTrue($registry->has('\\App\\Extensions\\'));
    }

    /**
     * all returns all namespaces in registration order.
     */
    public function test_all_returns_namespaces_in_order(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions');
        $registry->register('Vendor\\Package\\Extensions');

        $this->assertSame(['App\\Extensions', 'Vendor\\Package\\Extensions'], $registry->all());
    }

    /**
     * clear removes all namespaces and returns self.
     */
    public function test_clear_removes_all_and_returns_self(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions')->register('Vendor\\Extensions');

        $result = $registry->clear();

        $this->assertSame($registry, $result);
        $this->assertSame([], $registry->all());
    }

    /**
     * all returns empty array when no namespaces registered.
     */
    public function test_all_returns_empty_when_empty(): void
    {
        $registry = new ExtensionNamespaceRegistry;

        $this->assertSame([], $registry->all());
    }

    /**
     * register unregister then register same namespace works.
     */
    public function test_register_after_unregister_same_namespace(): void
    {
        $registry = new ExtensionNamespaceRegistry;
        $registry->register('App\\Extensions')->unregister('App\\Extensions')->register('App\\Extensions');

        $this->assertSame(['App\\Extensions'], $registry->all());
    }
}
