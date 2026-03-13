<?php

namespace JobMetric\Extension\Events\Plugin;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Models\Plugin;

readonly class PluginEditEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param Plugin $plugin
     */
    public function __construct(
        public Plugin $plugin
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'plugin.edited';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.plugin.group', 'extension::base.events.plugin.edited.title', 'extension::base.events.plugin.edited.description', 'fas fa-edit', [
            'plugin',
            'storage',
            'management',
        ]);
    }
}
