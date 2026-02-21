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
    protected static function getFacadeAccessor(): string
    {
        return 'ExtensionNamespaceRegistry';
    }
}
