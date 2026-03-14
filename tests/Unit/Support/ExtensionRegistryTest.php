<?php

namespace JobMetric\Extension\Tests\Unit\Support;

use JobMetric\Extension\Support\ExtensionRegistry;
use JobMetric\Extension\Tests\TestCase;

/**
 * Tests for ExtensionRegistry.
 */
class ExtensionRegistryTest extends TestCase
{
    /**
     * register adds namespace under type and returns self.
     */
    public function test_register_adds_namespace_under_type_and_returns_self(): void
    {
        $registry = new ExtensionRegistry;

        $result = $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame($registry, $result);
        $this->assertSame(['App\\Extensions\\Module\\Banner\\Banner'], $registry->byType('Module'));
    }

    /**
     * register normalizes type to StudlyCase.
     */
    public function test_register_normalizes_type_to_studly(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame(['App\\Extensions\\Module\\Banner\\Banner'], $registry->byType('Module'));
        $this->assertSame(['App\\Extensions\\Module\\Banner\\Banner'], $registry->byType('module'));
    }

    /**
     * register does not add duplicate namespace for same type.
     */
    public function test_register_does_not_add_duplicate_namespace(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame(['App\\Extensions\\Module\\Banner\\Banner'], $registry->byType('Module'));
    }

    /**
     * register adds multiple namespaces under same type.
     */
    public function test_register_adds_multiple_namespaces_under_same_type(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');
        $registry->register('Module', 'App\\Extensions\\Module\\Slider\\Slider');

        $this->assertSame([
                'App\\Extensions\\Module\\Banner\\Banner',
                'App\\Extensions\\Module\\Slider\\Slider',
            ], $registry->byType('Module'));
    }

    /**
     * unregister removes namespace and returns self.
     */
    public function test_unregister_removes_namespace_and_returns_self(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $result = $registry->unregister('App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame($registry, $result);
        $this->assertSame([], $registry->byType('Module'));
        $this->assertFalse($registry->has('App\\Extensions\\Module\\Banner\\Banner'));
    }

    /**
     * unregister removes type key when last namespace removed.
     */
    public function test_unregister_removes_type_when_empty(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $registry->unregister('App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame([], $registry->all());
    }

    /**
     * unregister on non-existent namespace leaves registry unchanged.
     */
    public function test_unregister_on_missing_namespace_does_nothing(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $registry->unregister('App\\Extensions\\Module\\Other\\Other');

        $this->assertSame(['Module' => ['App\\Extensions\\Module\\Banner\\Banner']], $registry->all());
    }

    /**
     * has returns true when namespace is registered.
     */
    public function test_has_returns_true_when_registered(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertTrue($registry->has('App\\Extensions\\Module\\Banner\\Banner'));
    }

    /**
     * has returns false when namespace is not registered.
     */
    public function test_has_returns_false_when_not_registered(): void
    {
        $registry = new ExtensionRegistry;

        $this->assertFalse($registry->has('App\\Extensions\\Module\\Banner\\Banner'));
    }

    /**
     * get returns type for registered namespace.
     */
    public function test_get_returns_type_for_namespace(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame('Module', $registry->get('App\\Extensions\\Module\\Banner\\Banner'));
    }

    /**
     * get returns null when namespace is not registered.
     */
    public function test_get_returns_null_when_not_registered(): void
    {
        $registry = new ExtensionRegistry;

        $this->assertNull($registry->get('App\\Extensions\\Module\\Banner\\Banner'));
    }

    /**
     * all returns full map of type => namespaces.
     */
    public function test_all_returns_full_map(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');
        $registry->register('Module', 'App\\Extensions\\Module\\Slider\\Slider');
        $registry->register('ShippingMethod', 'Vendor\\Shipping\\FlatRate\\FlatRate');

        $this->assertSame([
            'Module' => [
                'App\\Extensions\\Module\\Banner\\Banner',
                'App\\Extensions\\Module\\Slider\\Slider',
            ],
            'ShippingMethod' => ['Vendor\\Shipping\\FlatRate\\FlatRate'],
        ], $registry->all());
    }

    /**
     * values returns flat list of all namespaces.
     */
    public function test_values_returns_flat_list_of_namespaces(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');
        $registry->register('Module', 'App\\Extensions\\Module\\Slider\\Slider');

        $namespaces = $registry->values();

        $this->assertCount(2, $namespaces);
        $this->assertContains('App\\Extensions\\Module\\Banner\\Banner', $namespaces);
        $this->assertContains('App\\Extensions\\Module\\Slider\\Slider', $namespaces);
    }

    /**
     * byType returns namespaces for type and normalizes type to Studly.
     */
    public function test_byType_returns_namespaces_and_normalizes_type(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame(['App\\Extensions\\Module\\Banner\\Banner'], $registry->byType('module'));
        $this->assertSame([], $registry->byType('Unknown'));
    }

    /**
     * byTypeAndName finds namespace by type and extension name (class name).
     */
    public function test_byTypeAndName_finds_namespace_by_type_and_name(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');
        $registry->register('Module', 'App\\Extensions\\Module\\Slider\\Slider');

        $this->assertSame('App\\Extensions\\Module\\Banner\\Banner', $registry->byTypeAndName('Module', 'Banner'));
        $this->assertSame('App\\Extensions\\Module\\Slider\\Slider', $registry->byTypeAndName('Module', 'Slider'));
    }

    /**
     * byTypeAndName normalizes type and name to Studly.
     */
    public function test_byTypeAndName_normalizes_type_and_name(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertSame('App\\Extensions\\Module\\Banner\\Banner', $registry->byTypeAndName('module', 'banner'));
    }

    /**
     * byTypeAndName returns null when not found.
     */
    public function test_byTypeAndName_returns_null_when_not_found(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $this->assertNull($registry->byTypeAndName('Module', 'Other'));
        $this->assertNull($registry->byTypeAndName('Unknown', 'Banner'));
    }

    /**
     * resolveSpec returns null for non-existent class.
     */
    public function test_resolveSpec_returns_null_for_non_existent_class(): void
    {
        $registry = new ExtensionRegistry;

        $this->assertNull($registry->resolveSpec('NonExistent\\Fake\\ExtensionClass'));
    }

    /**
     * clear removes all and returns self.
     */
    public function test_clear_removes_all_and_returns_self(): void
    {
        $registry = new ExtensionRegistry;
        $registry->register('Module', 'App\\Extensions\\Module\\Banner\\Banner');

        $result = $registry->clear();

        $this->assertSame($registry, $result);
        $this->assertSame([], $registry->all());
        $this->assertSame([], $registry->values());
        $this->assertFalse($registry->has('App\\Extensions\\Module\\Banner\\Banner'));
    }

    /**
     * all and values return empty when registry is empty.
     */
    public function test_all_and_values_empty_when_empty(): void
    {
        $registry = new ExtensionRegistry;

        $this->assertSame([], $registry->all());
        $this->assertSame([], $registry->values());
    }
}
