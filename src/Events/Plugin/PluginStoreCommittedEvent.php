<?php

namespace JobMetric\Extension\Events\Plugin;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Plugin created outcome using allowlisted identifiers.
 */
readonly class PluginStoreCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param int|string $id
     *
     * @return void
     */
    public function __construct(
        public int|string $id
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'plugin.stored.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::automation.plugin_stored.group', 'extension::automation.plugin_stored.title', 'extension::automation.plugin_stored.description', 'fas fa-bolt', ['automation', 'committed', 'extension']);
    }
}
