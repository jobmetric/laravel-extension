<?php

namespace JobMetric\Extension\Tests\Unit\Models;

use JobMetric\Extension\Models\ExtensionMigration;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for ExtensionMigration model (table, fillable, no updated_at).
 */
class ExtensionMigrationModelTest extends TestCase
{
    /**
     * getTable returns config table name.
     */
    public function test_getTable_returns_config_value(): void
    {
        $this->assertSame('extension_migrations', (new ExtensionMigration)->getTable());
    }

    /**
     * updated_at column is null (no updated_at).
     */
    public function test_updated_at_is_null(): void
    {
        $this->assertNull((new ExtensionMigration)->getUpdatedAtColumn());
    }

    /**
     * fillable contains extension, name, migration.
     */
    public function test_fillable_contains_extension_name_migration(): void
    {
        $expected = ['extension', 'name', 'migration'];
        $this->assertSame($expected, (new ExtensionMigration)->getFillable());
    }

    /**
     * create persists and retrieve returns model.
     */
    public function test_create_and_retrieve_record(): void
    {
        $record = ExtensionMigration::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'migration' => '2023_01_01_000000_create_banners_table',
        ]);

        $this->assertGreaterThan(0, $record->id);
        $this->assertSame('Module', $record->extension);
        $this->assertSame('2023_01_01_000000_create_banners_table', $record->migration);
        $this->assertNotNull($record->created_at);
    }
}
