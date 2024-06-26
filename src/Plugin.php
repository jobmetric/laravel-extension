<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Validator;
use JobMetric\Extension\Events\PluginAddEvent;
use JobMetric\Extension\Events\PluginEditEvent;
use JobMetric\Extension\Exceptions\PluginNotFoundException;
use JobMetric\Extension\Facades\Extension as ExtensionFacade;
use JobMetric\Extension\Http\Requests\PluginRequest;
use JobMetric\Extension\Http\Resources\Fields\FieldResource;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
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
     * Get plugins
     *
     * @param int $plugin_id
     * @param bool $has_resource
     *
     * @return PluginModel|PluginResource
     * @throws Throwable
     */
    public function get(int $plugin_id, bool $has_resource = false): PluginModel|PluginResource
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
        $extension_model = ExtensionFacade::get($extension, $name);

        $fields = collect();

        $plugin_info = null;
        if ($plugin_id) {
            $plugin_info = $this->get($plugin_id);
        }

        $fields->add([
            'extension' => $extension,
            'extension_name' => $name,
            'name' => 'title',
            'type' => 'text',
            'required' => true,
            'value' => ($plugin_info) ? $plugin_info->title : null,
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
        $extension_model = ExtensionFacade::get($extension, $name);

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
            $plugin_model->title = $data['title'];
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
        $plugin_model = $this->get($plugin_id);

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

            $plugin_model->title = $data['title'];
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
     * @param int $plugin_id
     *
     * @return void
     */
    public function delete(int $plugin_id): void
    {
        // find the object plugin
        // delete plugin
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
            $fields_validation[$item['name']] = $item['validation'];
        }

        return $fields_validation;
    }
}
