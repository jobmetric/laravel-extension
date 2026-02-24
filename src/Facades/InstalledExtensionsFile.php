<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Support\InstalledExtensionsFile
 *
 * @method static string path()
 * @method static array<int, string> read()
 * @method static void syncFromDatabase(Application $app)
 */
class InstalledExtensionsFile extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'InstalledExtensionsFile';
    }
}
