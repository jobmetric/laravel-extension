<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Extension deleted outcome using allowlisted identifiers.
 */
readonly class ExtensionDeleteCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function __construct(
        public string $namespace
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'extension.deleted.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::automation.extension_deleted.group', 'extension::automation.extension_deleted.title', 'extension::automation.extension_deleted.description', 'fas fa-bolt', ['automation', 'committed', 'extension']);
    }
}
