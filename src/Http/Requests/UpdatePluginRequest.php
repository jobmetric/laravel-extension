<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Form\Http\Requests\FormBuilderRequest;
use Throwable;

/**
 * Form request for validating plugin updates.
 *
 * Builds validation rules from the extension's form definition (AbstractExtension::form())
 * and supports the dto() helper via rulesFor($input, $context) and setContext().
 * Context must provide extension_id and plugin so unique name rule can be applied correctly.
 */
class UpdatePluginRequest extends FormRequest
{
    /**
     * External context (injected via dto() or setExtensionId/setPlugin).
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Extension id of the plugin being updated (from route or setExtensionId/setContext).
     *
     * @var int|null
     */
    public int|null $extension_id = null;

    /**
     * Plugin model being updated (from route or setPlugin/setContext).
     *
     * @var Plugin|null
     */
    public Plugin|null $plugin = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Build validation rules from extension form. Used by dto() with context.
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $context ['extension_id' => int, 'plugin' => Plugin]
     *
     * @return array<string, ValidationRule|array|string>
     * @throws ExtensionNotFoundException
     * @throws Throwable
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        $extensionId = (int) ($context['extension_id'] ?? $input['extension_id'] ?? 0);
        if ($extensionId === 0) {
            throw new ExtensionNotFoundException;
        }

        $extension = Extension::find($extensionId);
        if (! $extension) {
            throw new ExtensionNotFoundException;
        }

        /** @var Plugin|null $plugin */
        $plugin = $context['plugin'] ?? null;

        $driver = static::resolveDriverStatic($extension);
        if (! $driver instanceof AbstractExtension) {
            throw new ExtensionNotFoundException;
        }

        $formRequest = new FormBuilderRequest($driver->form());
        $formRules = $formRequest->rules();

        $rules = [
            'status' => 'sometimes|boolean',
            'fields' => 'sometimes|array',
        ];

        $multiple = (bool) ($extension->info['multiple'] ?? false);
        if ($multiple && $plugin) {
            $rules['name'] = 'sometimes|string|max:255|unique:' . config('extension.tables.plugin') . ',name,' . $plugin->id . ',id,extension_id,' . $plugin->extension_id;
        }

        foreach ($formRules as $key => $rule) {
            $ruleArray = is_array($rule) ? $rule : explode('|', (string) $rule);
            if (! in_array('sometimes', $ruleArray)) {
                array_unshift($ruleArray, 'sometimes');
            }
            $rules['fields.' . $key] = $ruleArray;
        }

        return $rules;
    }

    /**
     * Get the validation rules (delegates to rulesFor with current input and context).
     *
     * @return array<string, ValidationRule|array|string>
     * @throws ExtensionNotFoundException
     * @throws Throwable
     */
    public function rules(): array
    {
        return self::rulesFor($this->all(), array_merge($this->context, [
            'extension_id' => $this->extension_id,
            'plugin'       => $this->plugin,
        ]));
    }

    /**
     * Get custom attributes for validator errors (labels for name, status, fields.* from extension form).
     *
     * @return array<string, string>
     * @throws BindingResolutionException
     */
    public function attributes(): array
    {
        $extension = $this->resolveExtension();
        if (! $extension) {
            return [
                'status' => trans('package-core::base.components.boolean_status.label'),
                'name'   => trans('extension::base.form.plugin.fields.name.title'),
            ];
        }

        $driver = $this->resolveDriver($extension);
        if (! $driver instanceof AbstractExtension) {
            return [
                'status' => trans('package-core::base.components.boolean_status.label'),
                'name'   => trans('extension::base.form.plugin.fields.name.title'),
            ];
        }

        $formRequest = new FormBuilderRequest($driver->form());
        $formAttributes = $formRequest->attributes();

        $attributes = [
            'status' => trans('package-core::base.components.boolean_status.label'),
            'name'   => trans('extension::base.form.plugin.fields.name.title'),
        ];
        foreach ($formAttributes as $key => $label) {
            $attributes['fields.' . $key] = $label;
        }

        return $attributes;
    }

    /**
     * Set extension id (used when request is built manually, e.g. in controller).
     *
     * @param int $extension_id
     *
     * @return $this
     */
    public function setExtensionId(int $extension_id): self
    {
        $this->extension_id = $extension_id;

        return $this;
    }

    /**
     * Set plugin model being updated (used when request is built manually).
     *
     * @param Plugin $plugin
     *
     * @return $this
     */
    public function setPlugin(Plugin $plugin): self
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Set context from dto() helper (extension_id, plugin and any extra keys).
     *
     * @param array<string, mixed> $context
     *
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
        $this->extension_id = isset($context['extension_id']) ? (int) $context['extension_id'] : $this->extension_id;
        $this->plugin = $context['plugin'] ?? $this->plugin;
    }

    /**
     * Resolve extension model from route parameter or extension_id.
     *
     * @return Extension|null
     */
    protected function resolveExtension(): Extension|null
    {
        $parameters = request()->route()?->parameters();
        $extension = $parameters['jm_extension'] ?? null;
        if ($extension instanceof Extension) {
            return $extension;
        }
        if ($this->extension_id) {
            return Extension::find($this->extension_id);
        }

        return null;
    }

    /**
     * Resolve plugin model from route parameter or setPlugin/setContext.
     *
     * @param Extension $extension Unused; kept for signature consistency with resolveExtension.
     *
     * @return Plugin|null
     */
    protected function resolvePlugin(Extension $extension): Plugin|null
    {
        $parameters = request()->route()?->parameters();
        $plugin = $parameters['jm_plugin'] ?? $this->plugin;
        if ($plugin instanceof Plugin) {
            return $plugin;
        }

        return null;
    }

    /**
     * Resolve extension driver (AbstractExtension instance) from extension model.
     *
     * @param Extension $extension
     *
     * @return object|null
     * @throws BindingResolutionException
     */
    protected function resolveDriver(Extension $extension): object|null
    {
        return static::resolveDriverStatic($extension);
    }

    /**
     * Resolve extension driver by class namespace from extension model.
     *
     * @param Extension $extension
     *
     * @return object|null
     * @throws BindingResolutionException
     */
    protected static function resolveDriverStatic(Extension $extension): object|null
    {
        $namespace = $extension->namespace;
        if (! class_exists($namespace)) {
            return null;
        }

        return app()->make($namespace);
    }
}
