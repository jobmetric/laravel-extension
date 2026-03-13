<?php

namespace JobMetric\Extension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Extension\Services\Extension
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response paginate(int $pageLimit = 15, array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response show(int $id, array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response all(array $filters = [], array $with = [], string|null $mode = null)
 *
 * @method static \JobMetric\Extension\Models\Extension|\JobMetric\Extension\Http\Resources\ExtensionResource getInfo(string $extension, string $name, bool $has_resource = false)
 * @method static \JobMetric\PackageCore\Output\Response install(string $namespace)
 * @method static \JobMetric\PackageCore\Output\Response uninstall(string $namespace, bool $force_delete_plugin = false)
 * @method static \JobMetric\PackageCore\Output\Response delete(string $type, string $namespace)
 * @method static \JobMetric\PackageCore\Output\Response upgrade(string $extension, string $name)
 * @method static bool isUpdated(string $extension, string $name)
 *
 * @method static void installZip(string $path, bool $delete_file = false)
 * @method static void download(string $url)
 * @method static void upload(string $path)
 *
 * @method static array<int, array<string, mixed>> getExtensionWithType(string $type)
 * @method static string namespaceFor(string $extension, string $name)
 */
class Extension extends Facade
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
        return 'Extension';
    }
}
