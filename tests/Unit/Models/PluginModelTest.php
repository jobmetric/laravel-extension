<?php

namespace JobMetric\Extension\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Extension\Tests\TestCase;
use ReflectionClass;

/**
 * Unit tests for Plugin model (table, fillable, casts, relation, touches).
 */
class PluginModelTest extends TestCase
{
    /**
     * getTable returns config table name.
     */
    public function test_getTable_returns_config_value(): void
    {
        $this->assertSame('plugins', (new Plugin)->getTable());
    }

    /**
     * fillable contains extension_id, name, fields, status.
     */
    public function test_fillable_contains_expected_attributes(): void
    {
        $expected = ['extension_id', 'name', 'fields', 'status'];
        $this->assertSame($expected, (new Plugin)->getFillable());
    }

    /**
     * casts contains fields as AsArrayObject and status as boolean.
     */
    public function test_casts_contains_fields_as_array_object_and_status_as_boolean(): void
    {
        $casts = (new Plugin)->getCasts();
        $this->assertSame(AsArrayObject::class, $casts['fields']);
        $this->assertSame('boolean', $casts['status']);
    }

    /**
     * touches array contains extension.
     */
    public function test_touches_contains_extension(): void
    {
        $plugin = new Plugin;
        $ref = new ReflectionClass($plugin);
        $prop = $ref->getProperty('touches');
        $this->assertSame(['extension'], $prop->getValue($plugin));
    }

    /**
     * extension relation returns BelongsTo Extension.
     */
    public function test_extension_relation_returns_belongs_to(): void
    {
        $plugin = new Plugin;
        $relation = $plugin->extension();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(Extension::class, $relation->getRelated()::class);
        $this->assertSame('extension_id', $relation->getForeignKeyName());
    }

    /**
     * create persists plugin and extension relation works.
     */
    public function test_create_plugin_linked_to_extension(): void
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
            'fields'       => ['key' => 'value'],
            'status'       => true,
        ]);

        $this->assertSame($ext->id, $plugin->extension_id);
        $this->assertTrue($plugin->status);
        $this->assertSame($ext->id, $plugin->extension->id);
    }
}
