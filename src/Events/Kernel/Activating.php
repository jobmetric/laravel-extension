<?php

namespace JobMetric\Extension\Events\Kernel;

use Illuminate\Foundation\Events\Dispatchable;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Kernel\ExtensionKernel;

readonly class Activating implements DomainEvent
{
    use Dispatchable;

    public function __construct(
        public ExtensionKernel $kernel
    ) {
    }

    public static function key(): string
    {
        return 'extension.kernel.activating';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.kernel.group', 'extension::base.events.kernel.activating.title', 'extension::base.events.kernel.activating.description', 'fas fa-bolt', [
            'extension',
            'kernel',
            'activate',
        ]);
    }
}
