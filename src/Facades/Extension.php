<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Extension\Extension
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filter = [])
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate(array $filter = [], int $page_limit = 15)
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection all(array $filter = [])
 * @method static \JobMetric\Extension\Models\Extension|\JobMetric\Extension\Http\Resources\ExtensionResource getInfo(string $extension, string $name, bool $has_resource = false)
 * @method static void install(string $extension, string $name)
 * @method static void uninstall(string $extension, string $name, bool $force_delete_plugin = false)
 * @method static void update(string $extension, string $name)
 * @method static void installZip(string $path, bool $delete_file = false)
 * @method static void download(string $path)
 * @method static void upload(string $path)
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
