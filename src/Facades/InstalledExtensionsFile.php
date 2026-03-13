<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Support\InstalledExtensionsFile
 *
 * @method static string path()
 * @method static array<int, string> read()
 * @method static void syncFromDatabase(\Illuminate\Contracts\Foundation\Application $app)
 */
class InstalledExtensionsFile extends Facade
{
    /**
     * Get the registered name of the component in the service container.
     *
     * This accessor must match the binding defined in the package service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'InstalledExtensionsFile';
    }
}
