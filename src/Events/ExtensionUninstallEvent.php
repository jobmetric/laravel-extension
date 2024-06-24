<?php

namespace JobMetric\Extension\Events;

use JobMetric\Extension\Models\Extension;

class ExtensionUninstallEvent
{
    public Extension $extension;

    /**
     * Create a new event instance.
     */
    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
