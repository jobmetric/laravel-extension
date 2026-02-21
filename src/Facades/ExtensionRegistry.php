<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the extension registry.
 *
 * Registry stores only type => [namespaces]. Use resolveSpec() when you need
 * the full spec (instantiate and call AbstractExtension::toArray()).
 *
 * @package JobMetric\Extension\Facades
 *
 * @mixin \JobMetric\Extension\Support\ExtensionRegistry
 *
 * @method static \JobMetric\Extension\Support\ExtensionRegistry register(string $type, string $namespace)
 * @method static \JobMetric\Extension\Support\ExtensionRegistry unregister(string $namespace)
 * @method static bool has(string $namespace)
 * @method static string|null get(string $namespace)
 * @method static array<string, array<int, string>> all()
 * @method static array<int, string> values()
 * @method static array<int, string> byType(string $type)
 * @method static string|null byTypeAndName(string $type, string $name)
 * @method static array<string, mixed>|null resolveSpec(string $namespace)
 * @method static \JobMetric\Extension\Support\ExtensionRegistry clear()
 */
class ExtensionRegistry extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ExtensionRegistry';
    }
}
