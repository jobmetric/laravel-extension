<?php

namespace JobMetric\Extension\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Events\Plugin\PluginDeleteEvent;
use JobMetric\Extension\Events\Plugin\PluginStoreEvent;
use JobMetric\Extension\Events\Plugin\PluginUpdateEvent;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Exceptions\PluginNotFoundException;
use JobMetric\Extension\Exceptions\PluginNotMatchExtensionException;
use JobMetric\Extension\Exceptions\PluginNotMultipleException;
use JobMetric\Extension\Facades\Extension as ExtensionFacade;
use JobMetric\Extension\Http\Requests\StorePluginRequest;
use JobMetric\Extension\Http\Requests\UpdatePluginRequest;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\Form\Form;
use JobMetric\Form\FormBuilder;
use JobMetric\Form\Support\IOForm;
use JobMetric\PackageCore\Output\Response;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * Class Plugin
 *
 * CRUD and plugin lifecycle service for Plugin entities.
 * Responsibilities:
 * - Validate store/update via form-based rules (StorePluginRequest / UpdatePluginRequest).
 * - Use AbstractCrudService store/update/destroy; validation in changeFieldStore/changeFieldUpdate.
 * - Expose storeForExtension/updateForExtension for (extension_id, data) API; add/edit; getInfo; fields; run
 * (AbstractExtension::handle).
 */
class Plugin extends AbstractCrudService
{
    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'extension::base.entity_names.plugin';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = PluginModel::class;
    protected static string $resourceClass = PluginResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'extension_id',
        'name',
        'fields',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Default sort applied by QueryBuilder.
     *
     * @var string[]
     */
    protected static array $defaultSort = ['name'];

    /**
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = PluginStoreEvent::class;
    protected static ?string $updateEventClass = PluginUpdateEvent::class;
    protected static ?string $deleteEventClass = PluginDeleteEvent::class;

    /**
     * Route store/update facade calls (extension_id, …) to storeForExtension/updateForExtension.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'store' && isset($arguments[0]) && is_int($arguments[0])) {
            $extensionId = $arguments[0];
            $data = $arguments[1] ?? [];
            $with = $arguments[2] ?? [];

            return $this->storeForExtension($extensionId, is_array($data) ? $data : [], is_array($with) ? $with : []);
        }

        if ($name === 'update' && count($arguments) >= 4 && is_int($arguments[0]) && is_int($arguments[1])) {
            $extensionId = $arguments[0];
            $pluginId = $arguments[1];
            $data = $arguments[2] ?? [];
            $with = $arguments[3] ?? [];

            return $this->updateForExtension($extensionId, $pluginId, is_array($data) ? $data : [], is_array($with) ? $with : []);
        }

        return parent::__call($name, $arguments);
    }

    /**
     * Build query with join to extension table for extra columns.
     *
     * @param array<string, mixed> $filters
     * @param array<int, string> $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filters = [], array $with = [], ?string $mode = null): QueryBuilder
    {
        $extensionTable = config('extension.tables.extension');
        $pluginTable = config('extension.tables.plugin');

        $base = PluginModel::query()->select([
            $pluginTable . '.*',
            'e.extension AS extension_type',
            'e.name AS extension_name',
            'e.namespace AS extension_namespace',
            'e.info AS extension_info',
        ])->leftJoin($extensionTable . ' AS e', 'e.id', '=', $pluginTable . '.extension_id');

        $qb = QueryBuilder::for(PluginModel::class)
            ->fromSub($base, $pluginTable)
            ->allowedFields(array_merge(static::$fields, [
                'extension_type',
                'extension_name',
                'extension_namespace',
                'extension_info',
            ]))
            ->allowedSorts(static::$fields)
            ->allowedFilters(static::$fields)
            ->defaultSort(static::$defaultSort)
            ->where($filters);

        $this->afterQuery($qb, $filters, $with, $mode);

        if ($with !== []) {
            $qb->with($with);
        }

        return $qb;
    }

    /**
     * Validate and normalize payload before create.
     *
     * Uses dto() with StorePluginRequest (form rules from AbstractExtension::form())
     * and IOForm::forStore to normalize fields for storage.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldStore(array &$data): void
    {
        $extensionId = isset($data['extension_id']) ? (int) $data['extension_id'] : null;
        if ($extensionId === null) {
            $data = array_intersect_key($data, array_flip([
                'extension_id',
                'name',
                'fields',
                'status',
            ]));

            if (isset($data['fields']) && ! is_array($data['fields'])) {
                $data['fields'] = [];
            }

            return;
        }

        $data = dto($data, StorePluginRequest::class, [
            'extension_id' => $extensionId,
        ]);

        $extension = ExtensionModel::find($extensionId);
        if ($extension !== null) {
            $driver = app()->make($extension->namespace);
            if ($driver instanceof AbstractExtension) {
                $data['fields'] = IOForm::forStore($driver->form(), $data['fields'] ?? []);
            }
        }

        $data = array_intersect_key($data, array_flip([
            'extension_id',
            'name',
            'fields',
            'status',
        ]));

        if (isset($data['fields']) && ! is_array($data['fields'])) {
            $data['fields'] = [];
        }
    }

    /**
     * Convenience: merge extension_id into data and call store(array $data, array $with).
     * Uses AbstractCrudService::store(); validation runs in changeFieldStore.
     *
     * @param int $extension_id
     * @param array<string, mixed> $data
     * @param array<int, string> $with
     *
     * @return Response
     * @throws Throwable
     */
    public function storeForExtension(int $extension_id, array $data, array $with = []): Response
    {
        $data['extension_id'] = $extension_id;

        return $this->store($data, $with);
    }

    /**
     * Add a plugin for extension (by type and name). Validates with form; enforces multiple when needed.
     *
     * @param string $extension            Extension type (e.g. Module).
     * @param string $name                 Extension name (e.g. Slider).
     * @param array<string, mixed> $fields name, status, fields (validated via StorePluginRequest).
     * @param array<int, string> $with
     *
     * @return Response
     * @throws ExtensionNotFoundException
     * @throws PluginNotMultipleException
     * @throws Throwable
     */
    public function add(string $extension, string $name, array $fields, array $with = []): Response
    {
        $extensionModel = ExtensionFacade::getInfo($extension, $name);
        if (! $extensionModel instanceof ExtensionModel) {
            throw new ExtensionNotFoundException();
        }

        $multiple = (bool) ($extensionModel->info['multiple'] ?? false);
        if (! $multiple) {
            $exists = PluginModel::query()->where('extension_id', $extensionModel->id)->exists();
            if ($exists) {
                throw new PluginNotMultipleException($extension, $name);
            }
        }

        return $this->storeForExtension($extensionModel->id, $fields, $with);
    }

    /**
     * Validate and normalize payload before update.
     *
     * Uses dto() with UpdatePluginRequest (form rules from AbstractExtension::form())
     * and IOForm::forStore to normalize fields for storage.
     *
     * @param Model $model The plugin model instance.
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var PluginModel $model */
        $data = dto($data, UpdatePluginRequest::class, [
            'extension_id' => $model->extension_id,
            'plugin'       => $model,
        ]);

        $extension = ExtensionModel::find($model->extension_id);
        if ($extension !== null) {
            $driver = app()->make($extension->namespace);
            if ($driver instanceof AbstractExtension && array_key_exists('fields', $data)) {
                $data['fields'] = IOForm::forStore($driver->form(), $data['fields'] ?? []);
            }
        }

        $data = array_intersect_key($data, array_flip(['name', 'fields', 'status']));
        if (array_key_exists('fields', $data) && ! is_array($data['fields'])) {
            $data['fields'] = [];
        }
    }

    /**
     * Convenience: ensure plugin belongs to extension, then call update(int $id, array $data, array $with).
     * Uses AbstractCrudService::update(); validation runs in changeFieldUpdate.
     *
     * @param int $extension_id
     * @param int $plugin_id
     * @param array<string, mixed> $data
     * @param array<int, string> $with
     *
     * @return Response
     * @throws PluginNotFoundException
     * @throws PluginNotMatchExtensionException
     * @throws Throwable
     */
    public function updateForExtension(int $extension_id, int $plugin_id, array $data, array $with = []): Response
    {
        $plugin = PluginModel::find($plugin_id);

        if ($plugin === null) {
            throw new PluginNotFoundException($plugin_id);
        }

        if ($plugin->extension_id !== $extension_id) {
            throw new PluginNotMatchExtensionException($extension_id, $plugin_id);
        }

        return $this->update($plugin_id, $data, $with);
    }

    /**
     * Edit a plugin by id. Validates with form (UpdatePluginRequest).
     *
     * @param int $plugin_id
     * @param array<string, mixed> $fields
     * @param array<int, string> $with
     *
     * @return Response
     * @throws PluginNotFoundException
     * @throws Throwable
     */
    public function edit(int $plugin_id, array $fields, array $with = []): Response
    {
        $plugin = PluginModel::with('extension')->find($plugin_id);
        if ($plugin === null) {
            throw new PluginNotFoundException($plugin_id);
        }

        return $this->update($plugin_id, $fields, $with);
    }

    /**
     * Get plugin by id; optionally as resource.
     *
     * @param int $plugin_id
     * @param bool $has_resource
     *
     * @return PluginModel|PluginResource
     * @throws PluginNotFoundException
     */
    public function getInfo(int $plugin_id, bool $has_resource = false): PluginModel|PluginResource
    {
        $plugin = PluginModel::with('extension')->find($plugin_id);
        if ($plugin === null) {
            throw new PluginNotFoundException($plugin_id);
        }

        return $has_resource ? PluginResource::make($plugin) : $plugin;
    }

    /**
     * Form definition and values for plugin add/edit from extension form().
     *
     * Uses Form package: form structure via Form::toArray(), values via IOForm::toArray()
     * so the consumer can render the form with the same structure as the Form package.
     *
     * @param string $extension   Extension type (e.g. Module).
     * @param string $name        Extension name (e.g. Slider).
     * @param int|null $plugin_id Existing plugin id for edit; null for add.
     *
     * @return array{form: array<string, mixed>, values: array<string, mixed>}
     * @throws Throwable
     */
    public function fields(string $extension, string $name, ?int $plugin_id = null): array
    {
        $extensionModel = ExtensionFacade::getInfo($extension, $name);
        if (! $extensionModel instanceof ExtensionModel) {
            return [
                'form'   => [],
                'values' => [],
            ];
        }

        $driver = app()->make($extensionModel->namespace);
        if (! $driver instanceof AbstractExtension) {
            return [
                'form'   => [],
                'values' => [],
            ];
        }

        $form = $this->resolveForm($driver->form());
        $pluginInfo = $plugin_id !== null ? $this->getInfo($plugin_id) : null;

        $extensionValues = IOForm::toArray($form, is_array($pluginInfo->fields ?? null) ? $pluginInfo->fields : []);

        $values = array_merge([
            'name'   => $pluginInfo?->name ?? '',
            'status' => $pluginInfo?->status ?? true,
        ], $extensionValues);

        return [
            'form'   => $form->toArray(),
            'values' => $values,
        ];
    }

    /**
     * Run the extension handle() for the given plugin (uses AbstractExtension::handle).
     *
     * @param int $plugin_id
     *
     * @return string|null
     * @throws PluginNotFoundException
     * @throws Throwable
     */
    public function run(int $plugin_id): ?string
    {
        $plugin = PluginModel::with('extension')->find($plugin_id);
        if ($plugin === null) {
            throw new PluginNotFoundException($plugin_id);
        }
        if (! $plugin->status) {
            return null;
        }

        $namespace = $plugin->extension->namespace ?? '';
        if ($namespace === '' || ! class_exists($namespace)) {
            return null;
        }

        $instance = app($namespace);
        if (! $instance instanceof AbstractExtension) {
            return null;
        }

        $fields = $plugin->fields;
        if (! is_array($fields)) {
            $fields = [];
        }

        return $instance->handle($fields);
    }

    /**
     * Resolve Form instance from FormBuilder or Form.
     *
     * @param FormBuilder|Form $form
     *
     * @return Form
     */
    protected function resolveForm(FormBuilder|Form $form): Form
    {
        return $form instanceof FormBuilder ? $form->build() : $form;
    }
}
