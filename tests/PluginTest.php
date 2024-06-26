<?php

namespace JobMetric\Extension\Tests;

use Illuminate\Support\Facades\DB;
use JobMetric\Extension\Exceptions\PluginNotMultipleException;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Facades\Plugin;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Plugin as PluginModel;
use Tests\BaseDatabaseTestCase as BaseTestCase;
use Throwable;

class PluginTest extends BaseTestCase
{
    /**
     * @throws Throwable
     */
    public function testAdd(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $plugin = Plugin::add('Addons', 'Banner', [
            'title' => 'sample title',
            'status' => true,
            'fields' => [
                'width' => '100',
                'height' => '100',
            ]
        ]);

        // Fetch the record from the database
        $record = DB::table('plugins')->where([
            'title' => 'sample title',
            'status' => true,
        ])->first();

        // Decode the fields to compare JSON structure
        $fields = json_decode($record->fields, true);
        $expectedFields = [
            'width' => '100',
            'height' => '100',
        ];

        // Assert that the fields match
        $this->assertEquals($expectedFields, $fields);

        $this->assertIsArray($plugin);
        $this->assertTrue($plugin['ok']);
        $this->assertEquals($plugin['message'], trans('extension::base.messages.plugin.added'));
        $this->assertInstanceOf(PluginResource::class, $plugin['data']);
        $this->assertEquals(201, $plugin['status']);

        // Test if plugin is not multiple
        try {
            $plugin = Plugin::add('Addons', 'Banner', [
                'title' => 'sample title',
                'status' => true,
                'fields' => [
                    'width' => '100',
                    'height' => '100',
                ]
            ]);

            $this->assertIsArray($plugin);
            $this->assertTrue($plugin['ok']);
            $this->assertEquals($plugin['message'], trans('extension::base.messages.plugin.added'));
            $this->assertInstanceOf(PluginResource::class, $plugin['data']);
            $this->assertEquals(201, $plugin['status']);
        } catch (Throwable $e) {
            $this->assertInstanceOf(PluginNotMultipleException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function testGet(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $plugin = Plugin::add('Addons', 'Banner', [
            'title' => 'sample title',
            'status' => 1,
            'fields' => [
                'width' => '100',
                'height' => '100',
            ]
        ]);

        $plugin_get = Plugin::get($plugin['data']->id);

        $this->assertInstanceOf(PluginModel::class, $plugin_get);
        $this->assertNotInstanceOf(PluginResource::class, $plugin_get);

        $plugin_get = Plugin::get($plugin['data']->id, true);

        $this->assertInstanceOf(PluginResource::class, $plugin_get);
        $this->assertNotInstanceOf(PluginModel::class, $plugin_get);
    }

    /**
     * @throws Throwable
     */
    public function testFields(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $plugin = Plugin::add('Addons', 'Banner', [
            'title' => 'sample title',
            'status' => 1,
            'fields' => [
                'width' => '100',
                'height' => '100',
            ]
        ]);

        // edit plugin
        $plugin_fields = Plugin::fields('Addons', 'Banner', $plugin['data']->id);

        $this->assertIsArray($plugin_fields);

        // title field
        $this->assertArrayHasKey('name', $plugin_fields[0]);
        $this->assertArrayHasKey('type', $plugin_fields[0]);
        $this->assertArrayHasKey('required', $plugin_fields[0]);
        $this->assertArrayHasKey('default', $plugin_fields[0]);
        $this->assertArrayHasKey('label', $plugin_fields[0]);
        $this->assertArrayHasKey('info', $plugin_fields[0]);
        $this->assertArrayHasKey('value', $plugin_fields[0]);

        $this->assertEquals('title', $plugin_fields[0]['name']);
        $this->assertEquals('text', $plugin_fields[0]['type']);
        $this->assertTrue($plugin_fields[0]['required']);
        $this->assertNull($plugin_fields[0]['default']);
        $this->assertEquals('sample title', $plugin_fields[0]['value']);

        // status field
        $this->assertArrayHasKey('name', $plugin_fields[1]);
        $this->assertArrayHasKey('type', $plugin_fields[1]);
        $this->assertArrayHasKey('required', $plugin_fields[1]);
        $this->assertArrayHasKey('default', $plugin_fields[1]);
        $this->assertArrayHasKey('label', $plugin_fields[1]);
        $this->assertArrayHasKey('info', $plugin_fields[1]);
        $this->assertArrayHasKey('value', $plugin_fields[1]);

        $this->assertEquals('status', $plugin_fields[1]['name']);
        $this->assertEquals('boolean', $plugin_fields[1]['type']);
        $this->assertTrue($plugin_fields[1]['required']);
        $this->assertTrue($plugin_fields[1]['default']);
        $this->assertTrue($plugin_fields[1]['value']);

        // width field
        $this->assertArrayHasKey('name', $plugin_fields[2]);
        $this->assertArrayHasKey('type', $plugin_fields[2]);
        $this->assertArrayHasKey('required', $plugin_fields[2]);
        $this->assertArrayHasKey('default', $plugin_fields[2]);
        $this->assertArrayHasKey('label', $plugin_fields[2]);
        $this->assertArrayHasKey('info', $plugin_fields[2]);
        $this->assertArrayHasKey('value', $plugin_fields[2]);

        $this->assertEquals('width', $plugin_fields[2]['name']);
        $this->assertEquals('number', $plugin_fields[2]['type']);
        $this->assertTrue($plugin_fields[2]['required']);
        $this->assertEquals('', $plugin_fields[2]['default']);
        $this->assertEquals(100, $plugin_fields[2]['value']);

        // height field
        $this->assertArrayHasKey('name', $plugin_fields[3]);
        $this->assertArrayHasKey('type', $plugin_fields[3]);
        $this->assertArrayHasKey('required', $plugin_fields[3]);
        $this->assertArrayHasKey('default', $plugin_fields[3]);
        $this->assertArrayHasKey('label', $plugin_fields[3]);
        $this->assertArrayHasKey('info', $plugin_fields[3]);
        $this->assertArrayHasKey('value', $plugin_fields[3]);

        $this->assertEquals('height', $plugin_fields[3]['name']);
        $this->assertEquals('number', $plugin_fields[3]['type']);
        $this->assertTrue($plugin_fields[3]['required']);
        $this->assertEquals('', $plugin_fields[3]['default']);
        $this->assertEquals(100, $plugin_fields[3]['value']);

        // add plugin
        $plugin_fields = Plugin::fields('Addons', 'Banner');

        $this->assertIsArray($plugin_fields);

        // title field
        $this->assertArrayHasKey('name', $plugin_fields[0]);
        $this->assertArrayHasKey('type', $plugin_fields[0]);
        $this->assertArrayHasKey('required', $plugin_fields[0]);
        $this->assertArrayHasKey('default', $plugin_fields[0]);
        $this->assertArrayHasKey('label', $plugin_fields[0]);
        $this->assertArrayHasKey('info', $plugin_fields[0]);
        $this->assertArrayHasKey('value', $plugin_fields[0]);

        $this->assertEquals('title', $plugin_fields[0]['name']);
        $this->assertEquals('text', $plugin_fields[0]['type']);
        $this->assertTrue($plugin_fields[0]['required']);
        $this->assertNull($plugin_fields[0]['default']);
        $this->assertNull($plugin_fields[0]['value']);

        // status field
        $this->assertArrayHasKey('name', $plugin_fields[1]);
        $this->assertArrayHasKey('type', $plugin_fields[1]);
        $this->assertArrayHasKey('required', $plugin_fields[1]);
        $this->assertArrayHasKey('default', $plugin_fields[1]);
        $this->assertArrayHasKey('label', $plugin_fields[1]);
        $this->assertArrayHasKey('info', $plugin_fields[1]);
        $this->assertArrayHasKey('value', $plugin_fields[1]);

        $this->assertEquals('status', $plugin_fields[1]['name']);
        $this->assertEquals('boolean', $plugin_fields[1]['type']);
        $this->assertTrue($plugin_fields[1]['required']);
        $this->assertTrue($plugin_fields[1]['default']);
        $this->assertNull($plugin_fields[1]['value']);

        // width field
        $this->assertArrayHasKey('name', $plugin_fields[2]);
        $this->assertArrayHasKey('type', $plugin_fields[2]);
        $this->assertArrayHasKey('required', $plugin_fields[2]);
        $this->assertArrayHasKey('default', $plugin_fields[2]);
        $this->assertArrayHasKey('label', $plugin_fields[2]);
        $this->assertArrayHasKey('info', $plugin_fields[2]);
        $this->assertArrayHasKey('value', $plugin_fields[2]);

        $this->assertEquals('width', $plugin_fields[2]['name']);
        $this->assertEquals('number', $plugin_fields[2]['type']);
        $this->assertTrue($plugin_fields[2]['required']);
        $this->assertEquals('', $plugin_fields[2]['default']);
        $this->assertNull($plugin_fields[2]['value']);

        // height field
        $this->assertArrayHasKey('name', $plugin_fields[3]);
        $this->assertArrayHasKey('type', $plugin_fields[3]);
        $this->assertArrayHasKey('required', $plugin_fields[3]);
        $this->assertArrayHasKey('default', $plugin_fields[3]);
        $this->assertArrayHasKey('label', $plugin_fields[3]);
        $this->assertArrayHasKey('info', $plugin_fields[3]);
        $this->assertArrayHasKey('value', $plugin_fields[3]);

        $this->assertEquals('height', $plugin_fields[3]['name']);
        $this->assertEquals('number', $plugin_fields[3]['type']);
        $this->assertTrue($plugin_fields[3]['required']);
        $this->assertEquals('', $plugin_fields[3]['default']);
        $this->assertNull($plugin_fields[3]['value']);
    }

    /**
     * @throws Throwable
     */
    public function testEdit(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $plugin = Plugin::add('Addons', 'Banner', [
            'title' => 'sample title',
            'status' => true,
            'fields' => [
                'width' => '100',
                'height' => '100',
            ]
        ]);

        $plugin_edit = Plugin::edit($plugin['data']->id, [
            'title' => 'sample title edit',
            'status' => false,
            'fields' => [
                'width' => '200',
                'height' => '200',
            ]
        ]);

        // Fetch the record from the database
        $record = DB::table('plugins')->where([
            'title' => 'sample title edit',
            'status' => false,
        ])->first();

        // Decode the fields to compare JSON structure
        $fields = json_decode($record->fields, true);
        $expectedFields = [
            'width' => '200',
            'height' => '200',
        ];

        // Assert that the fields match
        $this->assertEquals($expectedFields, $fields);

        $this->assertIsArray($plugin_edit);
        $this->assertTrue($plugin_edit['ok']);
        $this->assertEquals($plugin_edit['message'], trans('extension::base.messages.plugin.edited'));
        $this->assertInstanceOf(PluginResource::class, $plugin_edit['data']);
        $this->assertEquals(200, $plugin_edit['status']);
    }

    /**
     * @throws Throwable
     */
    public function testDelete(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $plugin = Plugin::add('Addons', 'Banner', [
            'title' => 'sample title',
            'status' => true,
            'fields' => [
                'width' => '100',
                'height' => '100',
            ]
        ]);

        $plugin_delete = Plugin::delete($plugin['data']->id);

        $this->assertIsArray($plugin_delete);
        $this->assertTrue($plugin_delete['ok']);
        $this->assertEquals($plugin_delete['message'], trans('extension::base.messages.plugin.deleted'));
        $this->assertInstanceOf(PluginResource::class, $plugin_delete['data']);
        $this->assertEquals(200, $plugin_delete['status']);
    }
}
