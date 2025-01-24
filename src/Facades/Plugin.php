<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Extension\Plugin
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filter = [], array $with = [])
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate(array $filter = [], int $page_limit = 15, array $with = [])
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection all(array $filter = [], array $with = [])
 * @method static array store(\JobMetric\Extension\Models\Extension $extension, array $data = [])
 * @method static \JobMetric\Extension\Models\Plugin|\JobMetric\Extension\Http\Resources\PluginResource getInfo(int $plugin_id, bool $has_resource = false)
 * @method static array fields(string $extension, string $name, int $plugin_id = null)
 * @method static array add(string $extension, string $name, array $fields)
 * @method static array edit(int $plugin_id, array $fields)
 * @method static array delete(int $plugin_id)
 * @method static string|null run(int $plugin_id)
 */
class Plugin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Extension\Plugin::class;
    }
}
