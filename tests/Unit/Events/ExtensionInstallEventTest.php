<?php

namespace JobMetric\Extension\Tests\Unit\Events;

use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Events\Extension\ExtensionInstallEvent;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for ExtensionInstallEvent (key, definition, constructor).
 */
class ExtensionInstallEventTest extends TestCase
{
    /**
     * key returns stable event key string.
     */
    public function test_key_returns_stable_string(): void
    {
        $this->assertSame('extension.installed', ExtensionInstallEvent::key());
    }

    /**
     * definition returns DomainEventDefinition instance.
     */
    public function test_definition_returns_domain_event_definition(): void
    {
        $def = ExtensionInstallEvent::definition();
        $this->assertInstanceOf(DomainEventDefinition::class, $def);
    }

    /**
     * Constructor accepts Extension model and optional data array.
     */
    public function test_constructor_accepts_extension_and_data(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => null,
        ]);

        $event = new ExtensionInstallEvent($ext, ['step' => 'migrate']);

        $this->assertSame($ext, $event->extension);
        $this->assertSame(['step' => 'migrate'], $event->data);
    }
}
