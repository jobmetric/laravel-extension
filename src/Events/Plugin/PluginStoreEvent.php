<?php

namespace JobMetric\Extension\Events\Plugin;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Models\Plugin;

readonly class PluginStoreEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param Plugin $plugin
     * @param array<string, mixed> $data
     */
    public function __construct(
        public Plugin $plugin,
        public array $data = []
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'plugin.stored';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.plugin.group', 'extension::base.events.plugin.stored.title', 'extension::base.events.plugin.stored.description', 'fas fa-save', [
            'plugin',
            'storage',
            'management',
        ]);
    }
}
