<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Support\ExtensionNamespaceRegistry
 *
 * @method static \JobMetric\Extension\Support\ExtensionNamespaceRegistry register(string $namespace)
 * @method static \JobMetric\Extension\Support\ExtensionNamespaceRegistry unregister(string $namespace)
 * @method static bool has(string $namespace)
 * @method static array<int, string> all()
 * @method static \JobMetric\Extension\Support\ExtensionNamespaceRegistry clear()
 */
class ExtensionNamespaceRegistry extends Facade
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
        return 'ExtensionNamespaceRegistry';
    }
}
