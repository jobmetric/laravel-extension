<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Models\Extension;

readonly class ExtensionInstallEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param Extension $extension
     * @param array<string, mixed> $data
     */
    public function __construct(
        public Extension $extension,
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
        return 'extension.installed';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.extension.group', 'extension::base.events.extension.installed.title', 'extension::base.events.extension.installed.description', 'fas fa-download', [
            'extension',
            'install',
            'management',
        ]);
    }
}
