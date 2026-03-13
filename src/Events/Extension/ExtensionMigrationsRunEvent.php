<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

readonly class ExtensionMigrationsRunEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param AbstractExtension $extension
     * @param array<int, string> $runMigrations Migration filenames that were run.
     */
    public function __construct(
        public AbstractExtension $extension,
        public array $runMigrations = []
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'extension.migrations_run';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.extension.group', 'extension::base.events.extension.migrations_run.title', 'extension::base.events.extension.migrations_run.description', 'fas fa-database', [
            'extension',
            'migrations',
            'management',
        ]);
    }
}
