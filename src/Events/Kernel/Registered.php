<?php

namespace JobMetric\Extension\Events\Kernel;

use Illuminate\Foundation\Events\Dispatchable;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Extension\Kernel\ExtensionKernel;

readonly class Registered implements DomainEvent
{
    use Dispatchable;

    public function __construct(
        public ExtensionKernel $kernel
    ) {
    }

    public static function key(): string
    {
        return 'extension.kernel.registered';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'extension::base.events.kernel.group', 'extension::base.events.kernel.registered.title', 'extension::base.events.kernel.registered.description', 'fas fa-check', [
            'extension',
            'kernel',
            'register',
        ]);
    }
}
