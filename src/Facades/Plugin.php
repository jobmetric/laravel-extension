<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Plugin
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filter = [], array $with = [])
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate(array $filter = [], int $page_limit = 15, array $with = [])
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection all(array $filter = [], array $with = [])
 *
 * @method static array store(int $extension_id, array $data = [])
 * @method static array update(int $extension_id, int $plugin_id, array $data = [])
 * @method static \JobMetric\Extension\Models\Plugin|\JobMetric\Extension\Http\Resources\PluginResource getInfo(int $plugin_id, bool $has_resource = false)
 *
 * @method static array fields(string $extension, string $name, int|null $plugin_id = null)
 * @method static array add(string $extension, string $name, array $fields)
 * @method static array edit(int $plugin_id, array $fields)
 * @method static array delete(int $plugin_id)
 * @method static string|null run(int $plugin_id)
 */
class Plugin extends Facade
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
        return 'Plugin';
    }
}
