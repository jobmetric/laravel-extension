<?php

namespace JobMetric\Extension\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for Extension model (table, fillable, casts, relations, scope).
 */
class ExtensionModelTest extends TestCase
{
    /**
     * getTable returns config table name.
     */
    public function test_getTable_returns_config_value(): void
    {
        $this->assertSame('extensions', (new Extension)->getTable());
    }

    /**
     * fillable contains extension, name, namespace, info.
     */
    public function test_fillable_contains_expected_attributes(): void
    {
        $expected = ['extension', 'name', 'namespace', 'info'];
        $this->assertSame($expected, (new Extension)->getFillable());
    }

    /**
     * casts contains info as AsArrayObject.
     */
    public function test_casts_contains_info_as_array_object(): void
    {
        $casts = (new Extension)->getCasts();
        $this->assertArrayHasKey('info', $casts);
        $this->assertSame(AsArrayObject::class, $casts['info']);
    }

    /**
     * plugins relation returns HasMany to Plugin.
     */
    public function test_plugins_relation_returns_has_many(): void
    {
        $extension = new Extension;
        $relation = $extension->plugins();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(Plugin::class, $relation->getRelated()::class);
        $this->assertSame('extension_id', $relation->getForeignKeyName());
    }

    /**
     * scopeWhereExtensionAndName filters by type and name.
     */
    public function test_scopeWhereExtensionAndName_filters_correctly(): void
    {
        Extension::query()->delete();
        Extension::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => ['version' => '1.0'],
        ]);
        Extension::create([
            'extension' => 'Module',
            'name'      => 'Slider',
            'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            'info'      => null,
        ]);

        $found = Extension::whereExtensionAndName('Module', 'Banner')->first();
        $this->assertNotNull($found);
        $this->assertSame('Banner', $found->name);

        $notFound = Extension::whereExtensionAndName('Module', 'Other')->first();
        $this->assertNull($notFound);
    }

    /**
     * create persists and retrieve returns model with info cast.
     */
    public function test_create_and_retrieve_extension(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'TestExt',
            'namespace' => 'App\\Extensions\\Module\\TestExt\\TestExt',
            'info'      => [
                'version' => '1.0',
                'title'   => 'Test',
            ],
        ]);

        $this->assertGreaterThan(0, $ext->id);
        $this->assertSame('Module', $ext->extension);
        $this->assertSame('TestExt', $ext->name);
        $this->assertIsArray($ext->info->toArray());
        $this->assertSame('1.0', $ext->info['version'] ?? null);
    }
}
