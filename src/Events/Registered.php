<?php

namespace JobMetric\Extension\Events;

use JobMetric\Extension\Kernel\ExtensionKernel;
use Illuminate\Foundation\Events\Dispatchable;

class Registered
{
    use Dispatchable;

    public function __construct(
        public ExtensionKernel $kernel
    ) {
    }
}
