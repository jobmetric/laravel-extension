<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Enums\ExtensionTypeEnum;
use JobMetric\Extension\Events\ExtensionInstallEvent;
use JobMetric\Extension\Events\ExtensionUninstallEvent;
use JobMetric\Extension\Events\PluginDeleteEvent;
use JobMetric\Extension\Exceptions\ExtensionAlreadyInstalledException;
use JobMetric\Extension\Exceptions\ExtensionClassNameNotMatchException;
use JobMetric\Extension\Exceptions\ExtensionConfigFileNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionConfigurationNotMatchException;
use JobMetric\Extension\Exceptions\ExtensionDontHaveContractException;
use JobMetric\Extension\Exceptions\ExtensionFolderNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionHaveSomePluginException;
use JobMetric\Extension\Exceptions\ExtensionNotInstalledException;
use JobMetric\Extension\Exceptions\ExtensionRunnerNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionTypeInvalidException;
use JobMetric\Extension\Facades\ExtensionType;
use JobMetric\Extension\Http\Resources\ExtensionResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class Extension
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Setting instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the specified extension.
     *
     * @param string $extension
     * @param array $filter
     * @param array $with
     *
     * @return QueryBuilder
     */
    private function query(string $extension, array $filter = [], array $with = []): QueryBuilder
    {
        $fields = ['id', 'extension', 'name', 'info', 'created_at', 'updated_at'];

        $query = QueryBuilder::for(ExtensionModel::class)
            ->select($fields)
            ->selectSub(function ($query) {
                $query->from(config('extension.tables.plugin'))
                    ->selectRaw('count(*)')
                    ->whereColumn('extension_id', 'extensions.id');
            }, 'plugin_count')
            ->allowedFields($fields)
            ->allowedSorts($fields)
            ->allowedFilters($fields)
            ->defaultSort([
                'extension',
                'name'
            ])
            ->where($filter);

        $query->where('extension', $extension);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * Get all extensions.
     *
     * @param string $type
     * @param array $filter
     * @param array $with
     *
     * @return AnonymousResourceCollection
     */
    public function all(string $type, array $filter = [], array $with = []): AnonymousResourceCollection
    {
        $database_extensions = $this->query(Str::studly($type), $filter, $with)->get();

        $extensions = $this->getExtensionWithType($type);
        foreach ($extensions as $i => $extension) {
            foreach ($database_extensions as $j => $database_extension) {
                if ($extension['extension'] === $database_extension->extension && $extension['name'] === $database_extension->info['name']) {
                    $extensions[$i]['data'] = $database_extension->toArray();
                    $extensions[$i]['installed'] = true;
                    unset($database_extensions[$j]);
                    break;
                }
            }
        }

        return ExtensionResource::collection($extensions);
    }

    /**
     * Get extension info.
     *
     * @param string $extension
     * @param string $name
     * @param bool $has_resource
     *
     * @return ExtensionModel|ExtensionResource
     * @throws Throwable
     */
    public function getInfo(string $extension, string $name, bool $has_resource = false): ExtensionModel|ExtensionResource
    {
        $extension_model = ExtensionModel::ExtensionName($extension, $name)->first();

        if (!$extension_model) {
            throw new ExtensionNotInstalledException($extension, $name);
        }

        if ($has_resource) {
            return ExtensionResource::make($extension_model);
        }

        return $extension_model;
    }

    /**
     * Extension installer
     *
     * @param string $extension
     * @param string $name
     *
     * @return array
     * @throws Throwable
     */
    public function install(string $extension, string $name): array
    {
        if (!in_array($extension, ExtensionTypeEnum::values())) {
            throw new ExtensionTypeInvalidException($extension, $name);
        }

        if (ExtensionModel::ExtensionName($extension, $name)->exists()) {
            throw new ExtensionAlreadyInstalledException($extension, $name);
        }

        $app_folder = appFolderName();
        $app_namespace = appNamespace();

        if (!is_dir(base_path("$app_folder/Extensions/$extension/$name"))) {
            throw new ExtensionFolderNotFoundException($extension, $name);
        }

        if (!file_exists(base_path("$app_folder/Extensions/$extension/$name/extension.json"))) {
            throw new ExtensionConfigFileNotFoundException($extension, $name);
        }

        $extension_information = json_decode(file_get_contents(base_path("$app_folder/Extensions/$extension/$name/extension.json")), true);

        if (!isset($extension_information['extension']) ||
            !isset($extension_information['name']) ||
            !isset($extension_information['version']) ||
            !isset($extension_information['title'])) {
            throw new ExtensionConfigurationNotMatchException($extension, $name);
        }

        if (!file_exists(base_path("$app_folder/Extensions/$extension/$name/$name.php"))) {
            throw new ExtensionRunnerNotFoundException($extension, $name);
        }

        $namespace = "{$app_namespace}Extensions\\$extension\\$name\\$name";

        // check class name
        if (!class_exists($namespace)) {
            throw new ExtensionClassNameNotMatchException($extension, $name);
        }

        // check class has implement ExtensionContract
        if (!in_array('JobMetric\Extension\Contracts\ExtensionContract', class_implements($namespace))) {
            throw new ExtensionDontHaveContractException($extension, $name);
        }

        // run install method
        if (method_exists($namespace, 'install')) {
            $namespace::install();
        }

        // create a new extension
        $extension_model = new ExtensionModel;

        $extension_model->extension = $extension;
        $extension_model->name = $name;
        $extension_model->info = $extension_information;

        $extension_model->save();

        event(new ExtensionInstallEvent($extension_model));

        return [
            'ok' => true,
            'message' => trans('extension::base.messages.extension.installed', [
                'extension' => $extension,
                'name' => $name
            ]),
            'data' => ExtensionResource::make($extension_model),
            'status' => 200
        ];
    }

    /**
     * Extension uninstaller
     *
     * @param string $extension
     * @param string $name
     * @param bool $force_delete_plugin
     *
     * @return array
     * @throws Throwable
     */
    public function uninstall(string $extension, string $name, bool $force_delete_plugin = false): array
    {
        $app_namespace = appNamespace();

        $extension_model = ExtensionModel::ExtensionName($extension, $name)->with('plugins')->first();

        if (!$extension_model) {
            throw new ExtensionNotInstalledException($extension, $name);
        }

        if (!$force_delete_plugin && $extension_model->plugins->count() > 0) {
            throw new ExtensionHaveSomePluginException($extension, $name);
        }

        $namespace = "{$app_namespace}Extensions\\$extension\\$name\\$name";

        if (method_exists($namespace, 'uninstall')) {
            $namespace::uninstall();
        }

        $data = ExtensionResource::make($extension_model);

        $extension_model->plugins()->get()->each(function ($plugin) {
            event(new PluginDeleteEvent($plugin));

            $plugin->delete();
        });

        $extension_model->delete();

        event(new ExtensionUninstallEvent($extension_model));

        return [
            'ok' => true,
            'message' => trans('extension::base.messages.extension.uninstalled', [
                'extension' => $extension,
                'name' => $name
            ]),
            'data' => $data,
            'status' => 200
        ];
    }

    /**
     * Extension updater
     *
     * @param string $extension
     * @param string $name
     *
     * @return array
     */
    public function update(string $extension, string $name): array
    {
        // check the version in extension.json local and remote repository
        // if the local version is lower than the remote version then run $this->download($path)
        return [];
    }

    /**
     * Check extension is updated from remote repository
     *
     * @param string $extension
     * @param string $name
     *
     * @return bool
     */
    public function isUpdated(string $extension, string $name): bool
    {
        // check the version in extension.json local and remote repository
        // if the local version is lower than the remote version then return false
        // else return true
        return true;
    }

    /**
     * Extension installer with zip
     *
     * @param string $path
     * @param bool $delete_file
     *
     * @return void
     */
    public function installZip(string $path, bool $delete_file = false): void
    {
        // unzip file
        // read file extension.json for get extension and name
        // create folder in app/Extensions/{extension}/{name}
        // copy all files to app/Extensions/{extension}/{name}
        // run $this->install($extension, $name)
        // delete zip file if $delete_file is true
    }

    /**
     * Extension downloader
     *
     * @param string $path
     *
     * @return void
     */
    public function download(string $path): void
    {
        // download file
        // run $this->installZip($path, true)
    }

    /**
     * Extension uploader
     *
     * @param string $path
     *
     * @return void
     */
    public function upload(string $path): void
    {
        // upload file
        // run $this->installZip($path, true)
    }

    /**
     * Get extension with type
     *
     * @param string $type
     *
     * @return array
     */
    public function getExtensionWithType(string $type): array
    {
        $serviceType = ExtensionType::type($type);
        $formatType = Str::studly($type);

        $extensions = [];
        $serviceType->getDriverNamespace()->each(function ($option, $namespace) use (&$extensions, $formatType) {
            $path = resolveNamespacePath($namespace);

            if (is_dir($path)) {
                $realPath = realpath($path);
                $folders = array_map('basename', File::directories($realPath));

                foreach ($folders as $folder) {
                    $folder_children = array_map('basename', File::directories($realPath . DIRECTORY_SEPARATOR . $folder));

                    foreach ($folder_children as $folder_child) {
                        $extensionRunnerAddress = $realPath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $folder_child . DIRECTORY_SEPARATOR . $folder_child . '.php';
                        $extensionConfigAddress = $realPath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $folder_child . DIRECTORY_SEPARATOR . 'extension.json';

                        if (file_exists($extensionRunnerAddress) && file_exists($extensionConfigAddress)) {
                            $extensionConfig = json_decode(file_get_contents($extensionConfigAddress), true);

                            if (empty($extensionConfig['extension']) || $extensionConfig['extension'] !== $formatType) {
                                continue;
                            }

                            if (empty($extensionConfig['name']) || $extensionConfig['name'] !== $folder_child) {
                                continue;
                            }

                            if (empty($extensionConfig['title']) || empty($extensionConfig['version'])) {
                                continue;
                            }

                            if (isset($extensionConfig['fields'])) {
                                unset($extensionConfig['fields']);
                            }

                            if (empty($extensionConfig['multiple'])) {
                                $extensionConfig['multiple'] = false;
                            }

                            if (empty($extensionConfig['description'])) {
                                $extensionConfig['description'] = 'extension::base.extension.default_description';
                            }

                            $extensions[] = array_merge($extensionConfig, [
                                'namespace' => $namespace . '\\' . $folder . '\\' . $folder_child . '\\' . $folder_child,
                                'deletable' => $option['deletable'] ?? false,
                            ]);
                        }
                    }
                }
            }
        });

        return $extensions;
    }
}
