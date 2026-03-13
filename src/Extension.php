<?php

namespace JobMetric\Extension;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Events\Extension\ExtensionDeleteEvent;
use JobMetric\Extension\Events\Extension\ExtensionInstallEvent;
use JobMetric\Extension\Events\Extension\ExtensionUninstallEvent;
use JobMetric\Extension\Events\Plugin\PluginDeleteEvent;
use JobMetric\Extension\Exceptions\ExtensionAlreadyInstalledException;
use JobMetric\Extension\Exceptions\ExtensionClassNameNotMatchException;
use JobMetric\Extension\Exceptions\ExtensionConfigFileNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionConfigurationNotMatchException;
use JobMetric\Extension\Exceptions\ExtensionDontHaveContractException;
use JobMetric\Extension\Exceptions\ExtensionFolderNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionFromPackageNotDeletableException;
use JobMetric\Extension\Exceptions\ExtensionHaveSomePluginException;
use JobMetric\Extension\Exceptions\ExtensionNotInstalledException;
use JobMetric\Extension\Exceptions\ExtensionNotUninstalledException;
use JobMetric\Extension\Exceptions\ExtensionRunnerNotFoundException;
use JobMetric\Extension\Facades\ExtensionRegistry;
use JobMetric\Extension\Facades\ExtensionTypeRegistry;
use JobMetric\Extension\Facades\InstalledExtensionsFile;
use JobMetric\Extension\Http\Resources\ExtensionResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\PackageCore\Output\Response;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * Class Extension
 *
 * CRUD and lifecycle service for Extension entities.
 * Responsibilities:
 * - List extensions by type (installed, needs_update, details, plugins_count)
 * - Install / uninstall (run migrations, default plugin when not multiple, then store/destroy)
 * - Delete extension files from disk when uninstalled and under App\Extensions
 * - Upgrade to latest version from remote (placeholder)
 * - installZip, download, upload (placeholders)
 */
class Extension extends AbstractCrudService
{
    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'extension::base.entity_names.extension';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = ExtensionModel::class;
    protected static string $resourceClass = ExtensionResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'extension',
        'name',
        'namespace',
        'info',
        'created_at',
        'updated_at',
    ];

    protected static array $defaultSort = [
        'extension',
        'name',
    ];

    protected static ?string $storeEventClass = ExtensionInstallEvent::class;
    protected static ?string $deleteEventClass = ExtensionUninstallEvent::class;

    /**
     * Methods not exposed via __call; use install() / uninstall() instead of store / destroy for lifecycle.
     *
     * @var string[]
     */
    protected array $excepts = [
        'store',
        'destroy',
    ];

    /**
     * List extensions for a type. Merges discovered specs with DB rows.
     * Each item: installed (bool), needs_update (bool when installed), details, plugins_count (when installed).
     *
     * @param array<string, mixed> $filters
     * @param array<int, string> $with
     * @param string|null $mode
     *
     * @return Response
     */
    public function doAll(array $filters = [], array $with = [], ?string $mode = null): Response
    {
        $extension = $filters['extension'] ?? null;

        if ($extension === null || $extension === '') {
            return parent::all($filters, $with, $mode);
        }

        $formatType = Str::studly((string) $extension);
        if (! ExtensionTypeRegistry::has($formatType)) {
            return Response::make(true, null, ExtensionResource::collection([]));
        }

        $databaseExtensions = $this->query($filters, $with, $mode)->get();
        $specs = $this->getExtensionWithType($formatType);

        foreach ($specs as $i => $spec) {
            $specs[$i]['installed'] = false;
            $specs[$i]['needs_update'] = false;

            foreach ($databaseExtensions as $j => $row) {
                $match = ($spec['extension'] ?? '') === $row->extension && ($spec['name'] ?? '') === ($row->name ?? ($row->info['name'] ?? ''));
                if ($match) {
                    $specs[$i]['data'] = $row;
                    $specs[$i]['installed'] = true;
                    $specs[$i]['needs_update'] = ! $this->isUpdated((string) $spec['extension'], (string) $spec['name']);
                    $databaseExtensions->forget($j);
                    break;
                }
            }
        }

        return Response::make(true, null, ExtensionResource::collection($specs));
    }

    /**
     * Install extension: validate, run migrations, store record and default plugin (when not multiple).
     *
     * @param string $namespace FQCN of the extension class.
     *
     * @return Response
     * @throws Throwable
     */
    public function install(string $namespace): Response
    {
        $parsed = $this->parseNamespaceAndValidatePath($namespace);
        $folder = $parsed['folder'];
        $extension = $parsed['extension'];
        $name = $parsed['name'];

        if (ExtensionModel::whereNamespace($namespace)->exists()) {
            throw new ExtensionAlreadyInstalledException($name);
        }

        $info = $this->loadExtensionInfo($folder, $name);

        if (! is_file($folder . DIRECTORY_SEPARATOR . $name . '.php')) {
            throw new ExtensionRunnerNotFoundException($name);
        }

        if (! class_exists($namespace)) {
            throw new ExtensionClassNameNotMatchException($name);
        }

        if (! is_subclass_of($namespace, Contracts\AbstractExtension::class)) {
            throw new ExtensionDontHaveContractException($name);
        }

        $instance = app($namespace);
        if (method_exists($instance, 'install')) {
            $instance->install();
        }

        $data = [
            'namespace' => $namespace,
            'extension' => $extension,
            'name'      => $name,
            'info'      => $info,
        ];

        $response = $this->store($data, ['plugins']);

        InstalledExtensionsFile::syncFromDatabase(app());

        $data['resource'] = $response->data instanceof ExtensionResource
            ? $response->data->resolve(request())
            : null;

        $message = trans('extension::base.messages.extension.installed', [
            'name' => trans($info['title'] ?? $name),
        ]);

        return Response::make(true, $message, $data);
    }

    /**
     * Uninstall extension: rollback migrations, delete plugins, destroy extension record.
     *
     * @param string $namespace         FQCN of the extension class.
     * @param bool $force_delete_plugin When true, remove plugins even if extension allows multiple.
     *
     * @return Response
     * @throws Throwable
     */
    public function uninstall(string $namespace, bool $force_delete_plugin = false): Response
    {
        $parsed = $this->parseNamespaceAndValidatePath($namespace);
        $info = $this->loadExtensionInfo($parsed['folder'], $parsed['name']);
        $multiple = (bool) ($info['multiple'] ?? false);

        $model = ExtensionModel::whereNamespace($namespace)->with('plugins')->first();
        if ($model === null) {
            throw new ExtensionNotInstalledException($parsed['name']);
        }

        if ($multiple && ! $force_delete_plugin && $model->plugins->count() > 0) {
            throw new ExtensionHaveSomePluginException($parsed['name']);
        }

        return DB::transaction(function () use ($namespace, $model, $info, $parsed) {
            $model->loadCount('plugins');
            $resourceArray = ExtensionResource::make($model)->resolve(request());

            $instance = app($namespace);
            if (method_exists($instance, 'uninstall')) {
                $instance->uninstall();
            }

            $model->plugins->each(function ($plugin) {
                event(new PluginDeleteEvent($plugin));
                $plugin->delete();
            });

            $this->destroy($model->id);

            InstalledExtensionsFile::syncFromDatabase(app());

            $message = trans('extension::base.messages.extension.uninstalled', [
                'name' => trans($info['title'] ?? $model->name),
            ]);

            $data = [
                'namespace' => $namespace,
                'extension' => $parsed['extension'],
                'name'      => $parsed['name'],
                'info'      => $info,
                'resource'  => $resourceArray,
            ];

            return Response::make(true, $message, $data);
        });
    }

    /**
     * Delete extension files from disk. Allowed only when already uninstalled and under App\Extensions.
     *
     * @param string $type
     * @param string $namespace
     *
     * @return Response
     * @throws ExtensionConfigFileNotFoundException
     * @throws ExtensionConfigurationNotMatchException
     * @throws ExtensionFolderNotFoundException
     * @throws ExtensionFromPackageNotDeletableException
     * @throws ExtensionNotUninstalledException
     */
    public function delete(string $type, string $namespace): Response
    {
        $parsed = $this->parseNamespaceAndValidatePath($namespace);
        $info = $this->loadExtensionInfo($parsed['folder'], $parsed['name']);

        if (! str_starts_with($namespace, self::getAppExtensionsPrefix())) {
            throw new ExtensionFromPackageNotDeletableException($parsed['name']);
        }

        if (ExtensionModel::whereNamespace($namespace)->exists()) {
            throw new ExtensionNotUninstalledException($parsed['name']);
        }

        File::deleteDirectory($parsed['folder']);

        event(new ExtensionDeleteEvent(Str::studly($type), $namespace, $parsed['name']));

        $message = trans('extension::base.messages.extension.deleted', [
            'name' => trans($info['title'] ?? $parsed['name']),
        ]);

        $data = [
            'namespace' => $namespace,
            'extension' => $parsed['extension'],
            'name'      => $parsed['name'],
            'info'      => $info,
        ];

        return Response::make(true, $message, $data);
    }

    /**
     * Install from zip path.
     *
     * @param string $path
     * @param bool $delete_file
     *
     * @return void
     */
    public function installZip(string $path, bool $delete_file = false): void
    {
    }

    /**
     * Download from URL and install.
     *
     * @param string $url
     *
     * @return void
     */
    public function download(string $url): void
    {
    }

    /**
     * Upload zip and install.
     *
     * @param string $path
     *
     * @return void
     */
    public function upload(string $path): void
    {
    }

    /**
     * Upgrade extension to latest version from remote/server.
     *
     * @param string $extension
     * @param string $name
     *
     * @return Response
     */
    public function upgrade(string $extension, string $name): Response
    {
        return Response::make(true, trans('extension::base.messages.extension.upgraded', ['name' => $name]), null);
    }

    /**
     * Whether extension is considered up to date.
     *
     * @param string $extension
     * @param string $name
     *
     * @return bool
     */
    public function isUpdated(string $extension, string $name): bool
    {
        return true;
    }

    /**
     * Get extension record by type and name; optionally as resource.
     *
     * @param string $extension
     * @param string $name
     * @param bool $has_resource
     *
     * @return ExtensionModel|ExtensionResource
     * @throws ExtensionNotInstalledException
     */
    public function getInfo(
        string $extension,
        string $name,
        bool $has_resource = false
    ): ExtensionModel|ExtensionResource {
        $model = ExtensionModel::whereExtensionAndName($extension, $name)->withCount('plugins')->first();

        if ($model === null) {
            throw new ExtensionNotInstalledException($name);
        }

        return $has_resource ? ExtensionResource::make($model) : $model;
    }

    /**
     * Build the extension class FQCN from type and name (e.g. App\Extensions\Module\Slider\Slider).
     *
     * @param string $extension
     * @param string $name
     *
     * @return string
     */
    public static function namespaceFor(string $extension, string $name): string
    {
        $name = Str::studly($name);

        return self::getAppExtensionsPrefix() . Str::studly($extension) . '\\' . $name . '\\' . $name;
    }

    /**
     * Discovered extension specs for a type (from registry), with deletable flag.
     *
     * @param string $type
     *
     * @return array<int, array<string, mixed>>
     */
    public function getExtensionWithType(string $type): array
    {
        $formatType = Str::studly($type);
        $appPrefix = self::getAppExtensionsPrefix();
        $namespaces = ExtensionRegistry::byType($formatType);
        $out = [];

        foreach ($namespaces as $namespace) {
            $spec = ExtensionRegistry::resolveSpec($namespace);
            if ($spec === null) {
                continue;
            }

            $out[] = array_merge($spec, [
                'namespace' => $namespace,
                'deletable' => str_starts_with($namespace, $appPrefix),
            ]);
        }

        return $out;
    }

    /**
     * Add plugins_count subSelect and scope by extension when filter has 'extension'.
     *
     * @param QueryBuilder $query
     * @param array<string, mixed> $filters
     * @param array<int, string> $with
     * @param string|null $mode
     *
     * @return void
     */
    protected function afterQuery(
        QueryBuilder &$query,
        array $filters = [],
        array $with = [],
        ?string $mode = null
    ): void {
        $table = $this->model->getTable();

        $query->selectSub(function ($q) use ($table) {
            $q->from(config('extension.tables.plugin'))
                ->selectRaw('count(*)')
                ->whereColumn('extension_id', $table . '.id');
        }, 'plugins_count');

        if (isset($filters['extension']) && $filters['extension'] !== '') {
            $query->where($table . '.extension', Str::studly((string) $filters['extension']));
        }
    }

    /**
     * Ensure data has extension, name, namespace, info for fill; no validation (install() prepares data).
     *
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function changeFieldStore(array &$data): void
    {
        $data = array_intersect_key($data, array_flip([
            'extension',
            'name',
            'namespace',
            'info',
        ]));
    }

    /**
     * Create default plugin after extension record is stored when extension is not multiple.
     * Form field keys and default values are read from the extension's form() (FormBuilder), not from extension.json.
     *
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function afterStore(Model $model, array &$data): void
    {
        /** @var ExtensionModel $model */
        $info = $model->info ?? [];
        $multiple = (bool) ($info['multiple'] ?? false);

        if ($multiple) {
            return;
        }

        $namespace = $model->namespace;
        if (! is_string($namespace) || ! class_exists($namespace) || ! is_subclass_of($namespace, AbstractExtension::class)) {
            $model->plugins()->create([
                'name'   => $info['title'] ?? $model->name,
                'fields' => [],
                'status' => true,
            ]);

            return;
        }

        $instance = app($namespace);
        $fields = $this->getDefaultFieldsFromExtensionForm($instance);

        $model->plugins()->create([
            'name'   => $info['title'] ?? $model->name,
            'fields' => $fields,
            'status' => true,
        ]);
    }

    /**
     * Extract default field values from the extension's form definition (FormBuilder).
     * Keys are taken from each CustomField's params['name'], values from params['value'] or attributes['value'].
     *
     * @param AbstractExtension $instance
     *
     * @return array<string, mixed>
     */
    protected function getDefaultFieldsFromExtensionForm(AbstractExtension $instance): array
    {
        $fields = [];
        $form = $instance->form()->build();

        foreach ($form->getAllCustomFields(true) as $customField) {
            $name = $customField->params['name'] ?? null;
            if ($name === null || $name === '') {
                continue;
            }

            $value = $customField->params['value'] ?? ($customField->attributes['value'] ?? null);
            $fields[$name] = $value;
        }

        return $fields;
    }

    /**
     * Parse path into extension type, name, folder; ensure folder and extension.json exist.
     *
     * @param string $namespace
     *
     * @return array{extension: string, name: string, folder: string}
     * @throws ExtensionConfigFileNotFoundException
     * @throws ExtensionFolderNotFoundException
     */
    private function parseNamespaceAndValidatePath(string $namespace): array
    {
        $path = resolveNamespacePath($namespace);

        if ($path === null || $path === '') {
            throw new ExtensionFolderNotFoundException(class_basename($namespace));
        }

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (is_dir($path)) {
            $folder = rtrim($path, DIRECTORY_SEPARATOR);
            $name = basename($folder);
            $runnerPath = $folder . DIRECTORY_SEPARATOR . $name . '.php';
            if (! is_file($runnerPath)) {
                throw new ExtensionRunnerNotFoundException($name);
            }
        } else {
            $pathToFile = str_ends_with($path, '.php') ? $path : $path . '.php';
            if (! is_file($pathToFile)) {
                throw new ExtensionFolderNotFoundException(class_basename($namespace));
            }
            $folder = dirname($pathToFile);
            $name = basename($pathToFile, '.php');
        }

        $extension = basename(dirname($folder));

        if (! is_dir($folder)) {
            throw new ExtensionFolderNotFoundException($name);
        }

        $configPath = $folder . DIRECTORY_SEPARATOR . 'extension.json';
        if (! is_file($configPath)) {
            throw new ExtensionConfigFileNotFoundException($name);
        }

        return [
            'extension' => $extension,
            'name'      => $name,
            'folder'    => $folder,
        ];
    }

    /**
     * Load and decode extension.json; require extension, name, version, title.
     *
     * @param string $folder
     * @param string $name
     *
     * @return array<string, mixed>
     * @throws ExtensionConfigurationNotMatchException
     */
    private function loadExtensionInfo(string $folder, string $name): array
    {
        $raw = @file_get_contents($folder . DIRECTORY_SEPARATOR . 'extension.json');
        $info = is_string($raw) ? json_decode($raw, true) : null;
        $info = is_array($info) ? $info : [];

        foreach (['extension', 'name', 'version', 'title'] as $key) {
            if (! isset($info[$key])) {
                throw new ExtensionConfigurationNotMatchException($name);
            }
        }

        return $info;
    }

    private static function getAppExtensionsPrefix(): string
    {
        $root = function_exists('appNamespace') ? trim(appNamespace(), '\\') : 'App';

        return $root . '\\Extensions\\';
    }
}
