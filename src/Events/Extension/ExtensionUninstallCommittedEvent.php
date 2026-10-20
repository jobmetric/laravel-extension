<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Extension uninstalled outcome using allowlisted identifiers.
 */
readonly class ExtensionUninstallCommittedEvent implements DomainEvent
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
        return 'extension.uninstalled.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::automation.extension_uninstalled.group', 'extension::automation.extension_uninstalled.title', 'extension::automation.extension_uninstalled.description', 'fas fa-bolt', ['automation', 'committed', 'extension']);
    }
}
