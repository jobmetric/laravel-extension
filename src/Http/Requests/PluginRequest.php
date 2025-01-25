<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Models\Extension;
use Throwable;

class PluginRequest extends FormRequest
{
    public array $fields = [];
    public int|null $extension_id = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     * @throws Throwable
     */
    public function rules(): array
    {
        $parameters = request()->route()->parameters();

        $extension = $parameters['jm_extension'] ?? null;
        $plugin = $parameters['jm_plugin'] ?? null;

        $info = $extension?->info;

        if (empty($info)) {
            if (empty($this->extension_id)) {
                throw new ExtensionNotFoundException;
            }

            $extension = Extension::find($this->extension_id);

            if (!$extension) {
                throw new ExtensionNotFoundException;
            }

            $info = $extension->info;
        }

        $multiple = $info['multiple'] ?? false;

        foreach ($info['fields'] as $field) {
            $this->fields[$field['name']] = [
                'validation' => $field['validation'] ?? 'string|nullable|sometimes',
                'label' => $field['label'] ?? '',
            ];
        }

        $rules['status'] = 'boolean';

        if ($multiple) {
            if ($plugin) {
                $rules['name'] = 'required|string|max:255|unique:' . config('extension.tables.plugin') . ',name,' . $plugin->id . ',id,extension_id,' . $plugin->extension_id;
            } else {
                $rules['name'] = 'required|string|max:255|unique:' . config('extension.tables.plugin') . ',name,NULL,id,extension_id,' . $extension->id;
            }
        }

        if (!empty($this->fields)) {
            $rules['fields'] = 'array';

            foreach ($this->fields as $key => $field) {
                $rules['fields.' . $key] = $field['validation'];
            }
        }

        return $rules;
    }

    /**
     * Set the extension id.
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
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'status' => trans('package-core::base.components.boolean_status.label'),
            'name' => trans('extension::base.form.plugin.fields.name.title')
        ];

        foreach ($this->fields as $key => $field) {
            $attributes['fields.' . $key] = $field['label'] ? trans($field['label']) : $key;
        }

        return $attributes;
    }
}
