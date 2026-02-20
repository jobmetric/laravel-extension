<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Models\Extension;
use JobMetric\Form\Http\Requests\FormBuilderRequest;
use Throwable;

class StorePluginRequest extends FormRequest
{
    public int|null $extension_id = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules. Form field rules come from the extension's form() (FormBuilder).
     *
     * @return array<string, ValidationRule|array|string>
     * @throws Throwable
     */
    public function rules(): array
    {
        $extension = $this->resolveExtension();
        if (!$extension) {
            throw new ExtensionNotFoundException;
        }

        $driver = $this->resolveDriver($extension);
        if (!$driver instanceof AbstractExtension) {
            throw new ExtensionNotFoundException;
        }

        $formRequest = new FormBuilderRequest($driver->form());
        $formRules = $formRequest->rules();

        $rules = [
            'status' => 'boolean',
            'fields' => 'array',
        ];

        $multiple = $extension->info['multiple'] ?? false;
        if ($multiple) {
            $rules['name'] = 'required|string|max:255|unique:' . config('extension.tables.plugin') . ',name,NULL,id,extension_id,' . $extension->id;
        }

        foreach ($formRules as $key => $rule) {
            $rules['fields.' . $key] = $rule;
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $extension = $this->resolveExtension();
        if (!$extension) {
            return [
                'status' => trans('package-core::base.components.boolean_status.label'),
                'name' => trans('extension::base.form.plugin.fields.name.title'),
            ];
        }

        $driver = $this->resolveDriver($extension);
        if (!$driver instanceof AbstractExtension) {
            return [
                'status' => trans('package-core::base.components.boolean_status.label'),
                'name' => trans('extension::base.form.plugin.fields.name.title'),
            ];
        }

        $formRequest = new FormBuilderRequest($driver->form());
        $formAttributes = $formRequest->attributes();

        $attributes = [
            'status' => trans('package-core::base.components.boolean_status.label'),
            'name' => trans('extension::base.form.plugin.fields.name.title'),
        ];
        foreach ($formAttributes as $key => $label) {
            $attributes['fields.' . $key] = $label;
        }

        return $attributes;
    }

    public function setExtensionId(int $extension_id): self
    {
        $this->extension_id = $extension_id;

        return $this;
    }

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
     * @return AbstractExtension|object|null
     */
    protected function resolveDriver(Extension $extension): object|null
    {
        $namespace = $extension->namespace;
        if (!class_exists($namespace)) {
            return null;
        }

        return app()->make($namespace);
    }
}
