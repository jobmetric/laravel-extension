<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Models\Extension;
use JobMetric\Form\Http\Requests\FormBuilderRequest;
use Throwable;

/**
 * Form request for validating plugin creation (store).
 *
 * Builds validation rules from the extension's form definition (AbstractExtension::form())
 * and supports the dto() helper via rulesFor($input, $context) and setContext().
 * Context must provide extension_id so rules and attributes can resolve the extension driver.
 */
class StorePluginRequest extends FormRequest
{
    /**
     * External context (injected via dto() or setExtensionId).
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Extension id for which the plugin is being created (from route or setExtensionId/setContext).
     *
     * @var int|null
     */
    public int|null $extension_id = null;

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
     * @param array<string, mixed> $context ['extension_id' => int]
     *
     * @return array<string, ValidationRule|array|string>
     * @throws ExtensionNotFoundException
     * @throws Throwable
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        $extensionId = (int) ($context['extension_id'] ?? $input['extension_id'] ?? 0);
        if ($extensionId === 0) {
            throw new ExtensionNotFoundException();
        }

        $extension = Extension::find($extensionId);
        if (! $extension) {
            throw new ExtensionNotFoundException();
        }

        $driver = static::resolveDriverStatic($extension);
        if (! $driver instanceof AbstractExtension) {
            throw new ExtensionNotFoundException();
        }

        $formRequest = new FormBuilderRequest($driver->form());
        $formRules = $formRequest->rules();

        $rules = [
            'extension_id' => 'sometimes|integer',
            'status'       => 'boolean',
            'fields'       => 'array',
        ];

        $multiple = (bool) ($extension->info['multiple'] ?? false);
        if ($multiple) {
            $rules['name'] = 'required|string|max:255|unique:' . config('extension.tables.plugin') . ',name,NULL,id,extension_id,' . $extension->id;
        }

        foreach ($formRules as $key => $rule) {
            $rules['fields.' . $key] = $rule;
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
        return self::rulesFor($this->all(), array_merge($this->context, ['extension_id' => $this->extension_id]));
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
     * Set context from dto() helper (extension_id and any extra keys).
     *
     * @param array<string, mixed> $context
     *
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
        $this->extension_id = isset($context['extension_id']) ? (int) $context['extension_id'] : $this->extension_id;
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
