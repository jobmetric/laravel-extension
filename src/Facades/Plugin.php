<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Services\Plugin
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response paginate(int $pageLimit = 15, array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response all(array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response show(int $id, array $with = [], string|null $mode = null)
 *
 * @method static \JobMetric\PackageCore\Output\Response store(array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response storeForExtension(int $extension_id, array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response update(int $id, array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response updateForExtension(int $extension_id, int $plugin_id, array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response destroy(int $id, array $with = [])
 * @method static \JobMetric\Extension\Models\Plugin|\JobMetric\Extension\Http\Resources\PluginResource getInfo(int $plugin_id, bool $has_resource = false)
 *
 * @method static array<int, array<string, mixed>> fields(string $extension, string $name, int|null $plugin_id = null)
 * @method static \JobMetric\PackageCore\Output\Response add(string $extension, string $name, array $fields, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response edit(int $plugin_id, array $fields, array $with = [])
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
