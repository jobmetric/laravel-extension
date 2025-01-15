<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Extension\ExtensionType
 *
 * @method static \JobMetric\Extension\ExtensionType define(string $type)
 * @method static \JobMetric\Extension\ExtensionType type(string $type)
 * @method static array getTypes()
 * @method static bool hasType(string $type)
 * @method static void checkType(string|null $type)
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
        return \JobMetric\Extension\ExtensionType::class;
    }
}
