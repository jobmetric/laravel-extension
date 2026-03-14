<?php

namespace JobMetric\Extension\Tests\Unit\Http\Requests;

use JobMetric\Extension\Http\Requests\UpdatePluginRequest;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for UpdatePluginRequest (authorize, setExtensionId, setPlugin, setContext).
 */
class UpdatePluginRequestTest extends TestCase
{
    /**
     * authorize returns true.
     */
    public function test_authorize_returns_true(): void
    {
        $request = new UpdatePluginRequest;
        $this->assertTrue($request->authorize());
    }

    /**
     * setExtensionId sets value and returns self.
     */
    public function test_setExtensionId_sets_value_and_returns_self(): void
    {
        $request = new UpdatePluginRequest;
        $result = $request->setExtensionId(5);
        $this->assertSame($request, $result);
        $this->assertSame(5, $request->extension_id);
    }

    /**
     * setPlugin sets plugin model and returns self.
     */
    public function test_setPlugin_sets_model_and_returns_self(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => null,
        ]);
        $plugin = Plugin::create([
            'extension_id' => $ext->id,
            'name'         => 'Main',
            'fields'       => null,
            'status'       => true,
        ]);

        $request = new UpdatePluginRequest;
        $result = $request->setPlugin($plugin);
        $this->assertSame($request, $result);
        $this->assertSame($plugin, $request->plugin);
    }

    /**
     * setContext sets extension_id and plugin from context.
     */
    public function test_setContext_sets_extension_id_and_plugin(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => null,
        ]);
        $plugin = Plugin::create([
            'extension_id' => $ext->id,
            'name'         => 'Main',
            'fields'       => null,
            'status'       => true,
        ]);

        $request = new UpdatePluginRequest;
        $request->setContext(['extension_id' => $ext->id, 'plugin' => $plugin]);
        $this->assertSame($ext->id, $request->extension_id);
        $this->assertSame($plugin, $request->plugin);
    }
}
