<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Models\Extension;

readonly class ExtensionUninstallEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param Extension $extension
     */
    public function __construct(
        public Extension $extension
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'extension.uninstalled';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.extension.group', 'extension::base.events.extension.uninstalled.title', 'extension::base.events.extension.uninstalled.description', 'fas fa-eject', [
            'extension',
            'uninstall',
            'management',
        ]);
    }
}
