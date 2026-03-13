<?php

namespace JobMetric\Extension\Events\Extension;

use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

readonly class ExtensionMigrationsRollbackEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param AbstractExtension $extension
     * @param array<int, string> $rollbackMigrations Migration filenames that were rolled back.
     */
    public function __construct(
        public AbstractExtension $extension,
        public array $rollbackMigrations = []
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'extension.migrations_rollback';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.extension.group', 'extension::base.events.extension.migrations_rollback.title', 'extension::base.events.extension.migrations_rollback.description', 'fas fa-undo', [
            'extension',
            'migrations',
            'management',
        ]);
    }
}
