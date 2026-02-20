<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Support\ExtensionTypeRegistry
 *
 * @method static \JobMetric\Extension\Support\ExtensionTypeRegistry register(string $type, array $options = [])
 * @method static \JobMetric\Extension\Support\ExtensionTypeRegistry unregister(string $type)
 * @method static bool has(string $type)
 * @method static array|null get(string $type)
 * @method static array all()
 * @method static array values()
 * @method static mixed getOption(string $type, string $key, mixed $default = null)
 * @method static \JobMetric\Extension\Support\ExtensionTypeRegistry clear()
 */
class ExtensionTypeRegistry extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ExtensionTypeRegistry';
    }
}
