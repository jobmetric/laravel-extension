<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Extension\Plugin
 *
 * @method static \JobMetric\Extension\Models\Plugin|\JobMetric\Extension\Http\Resources\PluginResource getInfo(int $plugin_id, bool $has_resource = false)
 * @method static array fields(string $extension, string $name, int $plugin_id = null)
 * @method static array add(string $extension, string $name, array $fields)
 * @method static array edit(int $plugin_id, array $fields)
 * @method static array delete(int $plugin_id)
 */
class Plugin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Extension\Plugin::class;
    }
}
