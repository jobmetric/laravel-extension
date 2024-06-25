<?php

namespace JobMetric\Extension\Tests;

use Illuminate\Support\Facades\DB;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Facades\Plugin;
use JobMetric\Extension\Http\Resources\PluginResource;
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
            'status' => 1,
            'fields' => [
                'width' => '100',
                'height' => '100',
            ]
        ]);

        // Fetch the record from the database
        $record = DB::table('plugins')->where([
            'title' => 'sample title',
            'status' => 1,
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
    }
}
