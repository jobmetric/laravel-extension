<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Extension\Events\PluginAddEvent;
use JobMetric\Extension\Events\PluginDeleteEvent;
use JobMetric\Extension\Events\PluginEditEvent;
use JobMetric\Extension\Events\PluginStoreEvent;
use JobMetric\Extension\Exceptions\PluginNotFoundException;
use JobMetric\Extension\Exceptions\PluginNotMultipleException;
use JobMetric\Extension\Facades\Extension as ExtensionFacade;
use JobMetric\Extension\Http\Requests\PluginRequest;
use JobMetric\Extension\Http\Resources\Fields\FieldResource;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class Plugin
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
     * Get the specified plugin.
     *
     * @param array $filter
     * @param array $with
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = []): QueryBuilder
    {
        $fields = ['id', 'extension_id', 'name', 'fields', 'status', 'created_at', 'updated_at'];

        $query = QueryBuilder::for(PluginModel::class)
            ->allowedFields($fields)
            ->allowedSorts($fields)
            ->allowedFilters($fields)
            ->defaultSort([
                'name'
            ])
            ->where($filter);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * Paginate the specified plugin.
     *
     * @param array $filter
     * @param int $page_limit
     * @param array $with
     *
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = [], int $page_limit = 15, array $with = []): LengthAwarePaginator
    {
        return $this->query($filter, $with)->paginate($page_limit);
    }

    /**
     * Get all plugins.
     *
     * @param array $filter
     * @param array $with
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = []): AnonymousResourceCollection
    {
        return PluginResource::collection(
            $this->query($filter, $with)->get()
        );
    }

    /**
     * Store a newly created plugin in storage.
     *
     * @param ExtensionModel $extension
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function store(ExtensionModel $extension, array $data): array
    {
        $validator = Validator::make($data, (new PluginRequest)->setExtensionId($extension->id)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('package-core::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();
        }

        return DB::transaction(function () use ($extension, $data) {
            $plugin = new PluginModel;

            $plugin->extension_id = $extension->id;
            $plugin->name = $data['name'];
            $plugin->fields = $data['fields'] ?? [];
            $plugin->status = $data['status'];

            $plugin->save();

            event(new PluginStoreEvent($plugin));

            return [
                'ok' => true,
                'message' => trans('extension::base.messages.plugin.stored'),
                'data' => PluginResource::make($plugin),
                'status' => 201
            ];
        });
    }

    /**
     * Get plugin info.
     *
     * @param int $plugin_id
     * @param bool $has_resource
     *
     * @return PluginModel|PluginResource
     * @throws Throwable
     */
    public function getInfo(int $plugin_id, bool $has_resource = false): PluginModel|PluginResource
    {
        /**
         * @var PluginModel $plugin_model
         */
        $plugin_model = PluginModel::with('extension')->find($plugin_id);

        if (!$plugin_model) {
            throw new PluginNotFoundException($plugin_id);
        }

        if ($has_resource) {
            return PluginResource::make($plugin_model);
        }

        return $plugin_model;
    }

    /**
     * Get fields
     *
     * @param string $extension
     * @param string $name
     * @param int|null $plugin_id
     *
     * @return array
     * @throws Throwable
     */
    public function fields(string $extension, string $name, int $plugin_id = null): array
    {
        /**
         * @var ExtensionModel $extension_model
         */
        $extension_model = ExtensionFacade::getInfo($extension, $name);

        $fields = collect();

        $plugin_info = null;
        if ($plugin_id) {
            $plugin_info = $this->getInfo($plugin_id);
        }

        $fields->add([
            'extension' => $extension,
            'extension_name' => $name,
            'name' => 'name',
            'type' => 'text',
            'required' => true,
            'value' => ($plugin_info) ? $plugin_info->name : null,
        ]);

        $fields->add([
            'extension' => $extension,
            'extension_name' => $name,
            'name' => 'status',
            'type' => 'boolean',
            'required' => true,
            'default' => true,
            'value' => ($plugin_info) ? $plugin_info->status : null,
        ]);

        foreach ($extension_model->info['fields'] as $item) {
            $fields->add(
                array_merge([
                    'extension' => $extension,
                    'extension_name' => $name,
                    'value' => ($plugin_info) ? $plugin_info->fields[$item['name']] : null,
                ], $item)
            );
        }

        return $fields->map(function ($item) {
            $class = 'JobMetric\\Extension\\Http\\Resources\\Fields\\' . ucfirst($item['type']) . 'FieldResource';

            if (class_exists($class)) {
                return $class::make($item)->toArray(request());
            }

            return FieldResource::make($item)->toArray(request());
        })->toArray();
    }

    /**
     * Add plugin
     *
     * @param string $extension
     * @param string $name
     * @param array $fields
     *
     * @return array
     * @throws Throwable
     */
    public function add(string $extension, string $name, array $fields): array
    {
        $extension_model = ExtensionFacade::getInfo($extension, $name);

        if (!$extension_model->info['multiple']) {
            $plugin_model = PluginModel::query()->where('extension_id', $extension_model->id)->first();
            if ($plugin_model) {
                throw new PluginNotMultipleException($extension, $name);
            }
        }

        $fields_validation = $this->fieldsValidation($extension_model);

        $validator = Validator::make($fields, (new PluginRequest)->setFields($fields_validation)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('extension::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();

            $plugin_model = new PluginModel;

            $plugin_model->extension_id = $extension_model->id;
            $plugin_model->name = $data['name'];
            $plugin_model->fields = $data['fields'] ?? [];
            $plugin_model->status = $data['status'];

            $plugin_model->save();

            event(new PluginAddEvent($plugin_model));

            return [
                'ok' => true,
                'message' => trans('extension::base.messages.plugin.added'),
                'data' => PluginResource::make($plugin_model),
                'status' => 201
            ];
        }
    }

    /**
     * Edit plugin
     *
     * @param int $plugin_id
     * @param array $fields
     *
     * @return array
     * @throws Throwable
     */
    public function edit(int $plugin_id, array $fields): array
    {
        $plugin_model = $this->getInfo($plugin_id);

        $fields_validation = $this->fieldsValidation($plugin_model->extension);

        $validator = Validator::make($fields, (new PluginRequest)->setFields($fields_validation)->rules());

        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('extension::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();

            $plugin_model->name = $data['name'];
            $plugin_model->fields = $data['fields'] ?? [];
            $plugin_model->status = $data['status'];

            $plugin_model->save();

            event(new PluginEditEvent($plugin_model));

            return [
                'ok' => true,
                'message' => trans('extension::base.messages.plugin.edited'),
                'data' => PluginResource::make($plugin_model),
                'status' => 200
            ];
        }
    }

    /**
     * Delete plugin
     *
     * @param int $plugin_id Plugin ID to delete
     *
     * @return array
     * @throws Throwable
     */
    public function delete(int $plugin_id): array
    {
        $plugin_model = $this->getInfo($plugin_id);

        $data = PluginResource::make($plugin_model);

        event(new PluginDeleteEvent($plugin_model));

        $plugin_model->delete();

        return [
            'ok' => true,
            'message' => trans('extension::base.messages.plugin.deleted'),
            'data' => $data,
            'status' => 200
        ];
    }

    /**
     * Run plugin
     *
     * @param int $plugin_id
     *
     * @return string|null
     * @throws Throwable
     */
    public function run(int $plugin_id): ?string
    {
        $plugin_model = $this->getInfo($plugin_id);

        if (!$plugin_model->status) {
            return null;
        }

        $extension_model = $plugin_model->extension;

        $class = '\\App\\Extensions\\' . $extension_model->extension . '\\' . $extension_model->name . '\\' . $extension_model->name;

        if (class_exists($class)) {
            $plugin = new $class();

            return $plugin->handle($plugin_model->fields);
        }

        return null;
    }

    /**
     * Get fields validation
     *
     * @param ExtensionModel $extension
     *
     * @return array
     */
    private function fieldsValidation(ExtensionModel $extension): array
    {
        $fields_validation = [];
        foreach ($extension->info['fields'] ?? [] as $item) {
            $fields_validation[$item['name']] = $item['validation'] ?? [];
        }

        return $fields_validation;
    }
}
