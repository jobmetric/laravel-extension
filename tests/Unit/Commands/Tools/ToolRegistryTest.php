<?php

namespace JobMetric\Extension\Tests\Unit\Commands\Tools;

use JobMetric\Extension\Commands\Tools\ToolRegistry;
use JobMetric\Extension\Tests\TestCase;
use stdClass;

/**
 * Unit tests for ToolRegistry (all, get, choices, register).
 */
class ToolRegistryTest extends TestCase
{
    /**
     * all returns array keyed by tool name with label, generator, options.
     */
    public function test_all_returns_array_keyed_by_tool_name(): void
    {
        $tools = ToolRegistry::all();
        $this->assertIsArray($tools);
        $this->assertArrayHasKey('model', $tools);
        $this->assertArrayHasKey('controller', $tools);
        $this->assertArrayHasKey('migration', $tools);
        $this->assertArrayHasKey('view', $tools);
    }

    /**
     * get returns config for registered tool.
     */
    public function test_get_returns_config_for_registered_tool(): void
    {
        $config = ToolRegistry::get('model');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('label', $config);
        $this->assertArrayHasKey('generator', $config);
        $this->assertArrayHasKey('options', $config);
        $this->assertSame('Eloquent model class', $config['label']);
    }

    /**
     * get returns null for unknown tool key.
     */
    public function test_get_returns_null_for_unknown_tool(): void
    {
        $this->assertNull(ToolRegistry::get('unknown_tool_key'));
    }

    /**
     * choices returns tool name to label map.
     */
    public function test_choices_returns_tool_name_to_label_map(): void
    {
        $choices = ToolRegistry::choices();

        $this->assertIsArray($choices);
        $this->assertArrayHasKey('model', $choices);
        $this->assertSame('Eloquent model class', $choices['model']);
    }

    /**
     * register adds or overrides tool by key.
     */
    public function test_register_adds_custom_tool(): void
    {
        $custom = [
            'label'     => 'Custom tool',
            'generator' => stdClass::class,
            'options'   => [],
        ];
        ToolRegistry::register(['custom_tool' => $custom]);

        $this->assertSame($custom, ToolRegistry::get('custom_tool'));
        $this->assertSame('Custom tool', ToolRegistry::choices()['custom_tool']);
    }
}
