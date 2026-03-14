<?php

namespace JobMetric\Extension\Tests\Feature\Http\Requests;

use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Http\Requests\UpdatePluginRequest;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\Form\FormBuilder;
use Throwable;

/**
 * Feature tests for UpdatePluginRequest::rulesFor in different situations.
 */
class UpdatePluginRequestRulesTest extends TestCase
{
    /**
     * rulesFor throws when extension_id in context is zero.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_id_in_context_is_zero(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        UpdatePluginRequest::rulesFor([], ['extension_id' => 0]);
    }

    /**
     * rulesFor throws when extension_id from input is zero.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_id_from_input_is_zero(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        UpdatePluginRequest::rulesFor(['extension_id' => 0], []);
    }

    /**
     * rulesFor throws when extension not in database.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_not_in_database(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        UpdatePluginRequest::rulesFor(['extension_id' => 99999], []);
    }

    /**
     * rulesFor throws when extension exists but driver class not loadable.
     *
     * @throws Throwable
     */
    public function test_rulesFor_throws_when_extension_driver_class_not_found(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'Missing',
            'namespace' => 'Vendor\\NonExistent\\Class',
            'info'      => [],
        ]);

        $this->expectException(ExtensionNotFoundException::class);

        UpdatePluginRequest::rulesFor([], ['extension_id' => $extension->id]);
    }

    /**
     * rulesFor returns rules when extension and driver available.
     *
     * @throws Throwable
     */
    public function test_rulesFor_returns_rules_when_extension_and_driver_available(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'Stub',
            'namespace' => StubExtensionForUpdateRequest::class,
            'info'      => ['multiple' => false],
        ]);

        $rules = UpdatePluginRequest::rulesFor([], ['extension_id' => $extension->id]);

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('fields', $rules);
        $this->assertSame('sometimes|boolean', $rules['status']);
    }

    /**
     * rulesFor includes name rule when extension is multiple and plugin in context.
     *
     * @throws Throwable
     */
    public function test_rulesFor_includes_name_rule_when_multiple_and_plugin_in_context(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'StubMultiple',
            'namespace' => StubExtensionForUpdateRequest::class,
            'info'      => ['multiple' => true],
        ]);
        $plugin = Plugin::create([
            'extension_id' => $extension->id,
            'name'         => 'Main',
            'fields'       => null,
            'status'       => true,
        ]);

        $rules = UpdatePluginRequest::rulesFor([], [
            'extension_id' => $extension->id,
            'plugin'       => $plugin,
        ]);

        $this->assertArrayHasKey('name', $rules);
        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('unique:', $rules['name']);
    }

    /**
     * rulesFor does not add name rule when plugin not in context for multiple extension.
     *
     * @throws Throwable
     */
    public function test_rulesFor_does_not_add_name_rule_when_plugin_missing_for_multiple(): void
    {
        $extension = Extension::create([
            'extension' => 'Module',
            'name'      => 'StubMultiple',
            'namespace' => StubExtensionForUpdateRequest::class,
            'info'      => ['multiple' => true],
        ]);

        $rules = UpdatePluginRequest::rulesFor([], ['extension_id' => $extension->id]);

        $this->assertArrayNotHasKey('name', $rules);
    }
}

class StubExtensionForUpdateRequest extends AbstractExtension
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
