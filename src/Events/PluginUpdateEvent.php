<?php

namespace JobMetric\Extension\Events;

use JobMetric\Extension\Models\Plugin;

class PluginUpdateEvent
{
    public Plugin $plugin;

    /**
     * Create a new event instance.
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
