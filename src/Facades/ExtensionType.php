<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\ExtensionType
 *
 * @method static \JobMetric\Extension\ExtensionType define(string $type)
 * @method static \JobMetric\Extension\ExtensionType type(string $type)
 * @method static array get()
 * @method static array getTypes()
 * @method static bool hasType(string $type)
 * @method static void ensureTypeExists(string $type)
 */
class ExtensionType extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ExtensionType';
    }
}
