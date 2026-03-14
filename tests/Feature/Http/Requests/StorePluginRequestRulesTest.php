<?php

namespace JobMetric\Extension\Tests\Feature\Http\Requests;

use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Http\Requests\StorePluginRequest;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\Form\FormBuilder;
use Throwable;

/**
 * Feature tests for StorePluginRequest::rulesFor in different situations.
 */
class StorePluginRequestRulesTest extends TestCase
{
    /**
     * rulesFor throws when extension_id in context is zero.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_id_in_context_is_zero(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        StorePluginRequest::rulesFor([], ['extension_id' => 0]);
    }

    /**
     * rulesFor throws when extension_id from input is zero and context empty.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_id_from_input_is_zero(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        StorePluginRequest::rulesFor(['extension_id' => 0], []);
    }

    /**
     * rulesFor uses extension_id from input when context has no extension_id.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_id_from_input_not_in_database(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        StorePluginRequest::rulesFor(['extension_id' => 99999], []);
    }

    /**
     * rulesFor uses extension_id from context over input when both present.
     *
     * @throws Throwable
     */
    public function test_rulesFor_uses_context_extension_id_when_both_input_and_context_given(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        StorePluginRequest::rulesFor(['extension_id' => 1], ['extension_id' => 0]);
    }

    /**
     * rulesFor throws when extension exists in DB but namespace class does not exist.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_driver_class_not_found(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'MissingDriver',
            'namespace' => 'Vendor\\NonExistent\\ExtensionClass',
            'info'      => [],
        ]);

        $this->expectException(ExtensionNotFoundException::class);

        StorePluginRequest::rulesFor([], ['extension_id' => $extension->id]);
    }

    /**
     * rulesFor returns rules array when extension exists and driver is loadable.
     *
     * @throws Throwable
     */
    public function test_rulesFor_returns_rules_when_extension_and_driver_available(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'Stub',
            'namespace' => StubExtensionForStoreRequest::class,
            'info'      => ['multiple' => false],
        ]);

        $rules = StorePluginRequest::rulesFor([], ['extension_id' => $extension->id]);

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('extension_id', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('fields', $rules);
        $this->assertSame('sometimes|integer', $rules['extension_id']);
        $this->assertSame('boolean', $rules['status']);
    }

    /**
     * rulesFor includes name rule when extension has multiple plugins.
     *
     * @throws Throwable
     */
    public function test_rulesFor_includes_name_rule_when_extension_multiple(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'StubMultiple',
            'namespace' => StubExtensionForStoreRequest::class,
            'info'      => ['multiple' => true],
        ]);

        $rules = StorePluginRequest::rulesFor([], ['extension_id' => $extension->id]);

        $this->assertArrayHasKey('name', $rules);
        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('unique:', $rules['name']);
    }
}

class StubExtensionForStoreRequest extends AbstractExtension
{
    public function configuration(ExtensionCore $extension): void
    {
    }

    public function form(): FormBuilder
    {
        return new FormBuilder();
    }

    public function handle(array $options = []): ?string
    {
        return null;
    }
}
