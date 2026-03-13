<?php

namespace JobMetric\Extension\Tests\Support;

use JobMetric\Extension\Support\ExtensionTypeRegistry;
use JobMetric\Extension\Tests\TestCase;

/**
 * Tests for ExtensionTypeRegistry.
 */
class ExtensionTypeRegistryTest extends TestCase
{
    /**
     * register adds a type with options and returns self.
     */
    public function test_register_adds_type_with_options_and_returns_self(): void
    {
        $registry = new ExtensionTypeRegistry;

        $result = $registry->register('Module', [
            'label'       => 'Module',
            'description' => 'Extension module',
        ]);

        $this->assertSame($registry, $result);
        $this->assertSame(['label' => 'Module', 'description' => 'Extension module'], $registry->get('Module'));
    }

    /**
     * register with empty options adds type with empty array.
     */
    public function test_register_with_empty_options(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module');

        $this->assertTrue($registry->has('Module'));
        $this->assertSame([], $registry->get('Module'));
    }

    /**
     * register merges options when type already exists.
     */
    public function test_register_merges_options_for_existing_type(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module', [
            'label'       => 'Module',
            'description' => 'Old',
        ]);
        $registry->register('Module', [
            'description' => 'New',
            'deletable'   => true,
        ]);

        $this->assertSame(['label' => 'Module', 'description' => 'New', 'deletable' => true], $registry->get('Module'));
    }

    /**
     * unregister removes type and returns self.
     */
    public function test_unregister_removes_type_and_returns_self(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module')->register('ShippingMethod');

        $result = $registry->unregister('Module');

        $this->assertSame($registry, $result);
        $this->assertFalse($registry->has('Module'));
        $this->assertTrue($registry->has('ShippingMethod'));
        $this->assertSame(['ShippingMethod' => []], $registry->all());
    }

    /**
     * unregister on non-existent type does nothing.
     */
    public function test_unregister_on_missing_type_does_nothing(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module');

        $registry->unregister('Unknown');

        $this->assertSame(['Module' => []], $registry->all());
    }

    /**
     * has returns true when type is registered.
     */
    public function test_has_returns_true_when_registered(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module');

        $this->assertTrue($registry->has('Module'));
    }

    /**
     * has returns false when type is not registered.
     */
    public function test_has_returns_false_when_not_registered(): void
    {
        $registry = new ExtensionTypeRegistry;

        $this->assertFalse($registry->has('Module'));
    }

    /**
     * get returns options for registered type.
     */
    public function test_get_returns_options_when_registered(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module', [
            'label' => 'Module Label',
        ]);

        $this->assertSame([
            'label' => 'Module Label',
        ], $registry->get('Module'));
    }

    /**
     * get returns null when type is not registered.
     */
    public function test_get_returns_null_when_not_registered(): void
    {
        $registry = new ExtensionTypeRegistry();

        $this->assertNull($registry->get('Module'));
    }

    /**
     * all returns full map of type => options.
     */
    public function test_all_returns_full_map(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module', [
            'label' => 'Module',
        ]);
        $registry->register('ShippingMethod', [
            'label' => 'Shipping',
        ]);

        $this->assertSame([
            'Module'         => [
                'label' => 'Module',
            ],
            'ShippingMethod' => [
                'label' => 'Shipping',
            ],
        ], $registry->all());
    }

    /**
     * values returns list of registered type names.
     */
    public function test_values_returns_type_names(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module')->register('ShippingMethod');

        $this->assertSame(['Module', 'ShippingMethod'], $registry->values());
    }

    /**
     * getOption returns option value for registered type.
     */
    public function test_getOption_returns_value_when_key_exists(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module', [
            'label'       => 'Module Label',
            'description' => 'Desc',
        ]);

        $this->assertSame('Module Label', $registry->getOption('Module', 'label'));
        $this->assertSame('Desc', $registry->getOption('Module', 'description'));
    }

    /**
     * getOption returns default when key is missing.
     */
    public function test_getOption_returns_default_when_key_missing(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module', [
            'label' => 'Module',
        ]);

        $this->assertNull($registry->getOption('Module', 'missing'));
        $this->assertSame('default', $registry->getOption('Module', 'missing', 'default'));
    }

    /**
     * getOption returns default when type is not registered.
     */
    public function test_getOption_returns_default_when_type_not_registered(): void
    {
        $registry = new ExtensionTypeRegistry;

        $this->assertNull($registry->getOption('Module', 'label'));
        $this->assertSame('fallback', $registry->getOption('Module', 'label', 'fallback'));
    }

    /**
     * clear removes all types and returns self.
     */
    public function test_clear_removes_all_and_returns_self(): void
    {
        $registry = new ExtensionTypeRegistry;
        $registry->register('Module')->register('ShippingMethod');

        $result = $registry->clear();

        $this->assertSame($registry, $result);
        $this->assertSame([], $registry->all());
        $this->assertSame([], $registry->values());
        $this->assertFalse($registry->has('Module'));
    }

    /**
     * all returns empty array when no types registered.
     */
    public function test_all_returns_empty_when_empty(): void
    {
        $registry = new ExtensionTypeRegistry;

        $this->assertSame([], $registry->all());
        $this->assertSame([], $registry->values());
    }
}
