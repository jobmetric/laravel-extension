<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

readonly class ExtensionDeleteEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param string $type      Extension type (e.g. Module).
     * @param string $namespace Extension class FQCN that was deleted.
     * @param string $name      Extension name (e.g. Banner).
     */
    public function __construct(
        public string $type,
        public string $namespace,
        public string $name
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'extension.deleted';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.extension.group', 'extension::base.events.extension.deleted.title', 'extension::base.events.extension.deleted.description', 'fas fa-trash-alt', [
            'extension',
            'delete',
            'management',
        ]);
    }
}
