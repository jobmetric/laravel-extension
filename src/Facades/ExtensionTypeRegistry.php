<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Support\ExtensionTypeRegistry
 *
 * @method static \JobMetric\Extension\Support\ExtensionTypeRegistry register(string $type, array $options = [])
 * @method static \JobMetric\Extension\Support\ExtensionTypeRegistry unregister(string $type)
 * @method static bool has(string $type)
 * @method static array<string, mixed>|null get(string $type)
 * @method static array<string, mixed> all()
 * @method static array<int, string> values()
 * @method static mixed getOption(string $type, string $key, mixed $default = null)
 * @method static \JobMetric\Extension\Support\ExtensionTypeRegistry clear()
 */
class ExtensionTypeRegistry extends Facade
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
        return 'ExtensionTypeRegistry';
    }
}
