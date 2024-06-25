<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Validator;
use JobMetric\Extension\Events\PluginAddEvent;
use JobMetric\Extension\Exceptions\ExtensionNotInstalledException;
use JobMetric\Extension\Http\Requests\AddPluginRequest;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\Location\Http\Requests\StoreCountryRequest;
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
     * Add plugin
     *
     * @param string $extension
     * @param string $name
     * @param array $options
     *
     * @return array
     * @throws Throwable
     */
    public function add(string $extension, string $name, array $options): array
    {
        $extension_model = ExtensionModel::ExtensionName($extension, $name)->first();

        if (!$extension_model) {
            throw new ExtensionNotInstalledException($extension, $name);
        }

        $fields_validation = [];
        foreach ($extension_model->info['fields'] ?? [] as $item) {
            $fields_validation[$item['name']] = $item['validation'];
        }

        $validator = Validator::make($options, (new AddPluginRequest)->setFields($fields_validation)->rules());
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
     *
     * @return void
     */
    public function edit(int $plugin_id): void
    {
        // find the object plugin
        // read field configuration
        // check validation fields
        // edit field
        // save to database plugin
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
}
