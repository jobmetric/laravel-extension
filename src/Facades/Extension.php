<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Extension\Extension
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(string $extension, array $filter = [], array $with = [])
 * @method static array all(string $extension, array $filter = [], array $with = [])
 * @method static \JobMetric\Extension\Models\Extension|\JobMetric\Extension\Http\Resources\ExtensionResource getInfo(string $extension, string $name, bool $has_resource = false)
 * @method static array install(string $namespace)
 * @method static array uninstall(string $namespace, bool $force_delete_plugin = false)
 * @method static array update(string $extension, string $name)
 * @method static void installZip(string $path, bool $delete_file = false)
 * @method static void download(string $path)
 * @method static void upload(string $path)
 * @method static array getExtensionWithType(string $type)
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
