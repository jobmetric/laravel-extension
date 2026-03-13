<?php

namespace JobMetric\Extension\Tests\Services;

use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Exceptions\PluginNotFoundException;
use JobMetric\Extension\Exceptions\PluginNotMatchExtensionException;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\Extension\Services\Plugin;
use JobMetric\Extension\Tests\TestCase;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * Tests for Plugin service.
 */
class PluginTest extends TestCase
{
    /**
     * getInfo returns model when plugin exists.
     *
     * @throws PluginNotFoundException
     */
    public function test_getInfo_returns_model_when_plugin_exists(): void
    {
        $extension = ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);
        $plugin = PluginModel::create([
            'extension_id' => $extension->id,
            'name'         => 'Main',
            'fields'       => [],
            'status'       => true,
        ]);

        $service = $this->app->make(Plugin::class);
        $result = $service->getInfo($plugin->id, false);

        $this->assertInstanceOf(PluginModel::class, $result);
        $this->assertSame($plugin->id, $result->id);
        $this->assertSame('Main', $result->name);
    }

    /**
     * getInfo returns resource when has_resource is true.
     *
     * @throws PluginNotFoundException
     */
    public function test_getInfo_returns_resource_when_has_resource_true(): void
    {
        $extension = ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);
        $plugin = PluginModel::create([
            'extension_id' => $extension->id,
            'name'         => 'Main',
            'fields'       => [],
            'status'       => true,
        ]);

        $service = $this->app->make(Plugin::class);
        $result = $service->getInfo($plugin->id, true);

        $this->assertInstanceOf(PluginResource::class, $result);
    }

    /**
     * getInfo throws PluginNotFoundException when plugin does not exist.
     */
    public function test_getInfo_throws_when_plugin_not_found(): void
    {
        $this->expectException(PluginNotFoundException::class);

        $service = $this->app->make(Plugin::class);
        $service->getInfo(99999);
    }

    /**
     * run throws PluginNotFoundException when plugin does not exist.
     *
     * @throws Throwable
     */
    public function test_run_throws_when_plugin_not_found(): void
    {
        $this->expectException(PluginNotFoundException::class);

        $service = $this->app->make(Plugin::class);
        $service->run(99999);
    }

    /**
     * run returns null when plugin status is false.
     */
    public function test_run_returns_null_when_plugin_disabled(): void
    {
        $extension = ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);
        $plugin = PluginModel::create([
            'extension_id' => $extension->id,
            'name'         => 'Main',
            'fields'       => [],
            'status'       => false,
        ]);

        $service = $this->app->make(Plugin::class);
        $result = $service->run($plugin->id);

        $this->assertNull($result);
    }

    /**
     * updateForExtension throws PluginNotFoundException when plugin does not exist.
     */
    public function test_updateForExtension_throws_when_plugin_not_found(): void
    {
        $extension = ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);

        $this->expectException(PluginNotFoundException::class);

        $service = $this->app->make(Plugin::class);
        $service->updateForExtension($extension->id, 99999, ['name' => 'Updated']);
    }

    /**
     * updateForExtension throws PluginNotMatchExtensionException when plugin belongs to another extension.
     */
    public function test_updateForExtension_throws_when_extension_mismatch(): void
    {
        $ext1 = ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);
        $ext2 = ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Slider',
            'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            'info'      => [],
        ]);
        $plugin = PluginModel::create([
            'extension_id' => $ext2->id,
            'name'         => 'Main',
            'fields'       => [],
            'status'       => true,
        ]);

        $this->expectException(PluginNotMatchExtensionException::class);

        $service = $this->app->make(Plugin::class);
        $service->updateForExtension($ext1->id, $plugin->id, ['name' => 'Updated']);
    }

    /**
     * edit throws PluginNotFoundException when plugin does not exist.
     *
     * @throws Throwable
     */
    public function test_edit_throws_when_plugin_not_found(): void
    {
        $this->expectException(PluginNotFoundException::class);

        $service = $this->app->make(Plugin::class);
        $service->edit(99999, ['name' => 'Updated']);
    }

    /**
     * storeForExtension with non-existent extension_id triggers validation path (extension not found).
     *
     * @throws Throwable
     */
    public function test_storeForExtension_requires_valid_extension(): void
    {
        $this->expectException(ExtensionNotFoundException::class);

        $service = $this->app->make(Plugin::class);
        $service->storeForExtension(99999, [
            'name'   => 'Test',
            'fields' => [],
            'status' => true,
        ]);
    }

    /**
     * query returns QueryBuilder instance.
     */
    public function test_query_returns_query_builder(): void
    {
        $service = $this->app->make(Plugin::class);
        $qb = $service->query([], [], null);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }
}
