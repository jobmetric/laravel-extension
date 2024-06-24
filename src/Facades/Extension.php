<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Extension\Extension
 *
 * @method static void install(string $extension, string $name)
 * @method static void uninstall(string $extension, string $name, bool $force_delete_plugin = false)
 * @method static void update(string $extension, string $name)
 * @method static void installZip(string $path, bool $delete_file = false)
 * @method static void download(string $path)
 * @method static void upload(string $path)
 */
class Extension extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Extension\Extension::class;
    }
}
