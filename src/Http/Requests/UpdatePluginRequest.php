<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use Throwable;

class UpdatePluginRequest extends FormRequest
{
    public array $fields = [];
    public int|null $extension_id = null;
    public Plugin|null $plugin = null;

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
        $plugin = $parameters['jm_plugin'] ?? $this->plugin ?? null;

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

        $rules['status'] = 'boolean|sometimes';

        if ($multiple) {
            $rules['name'] = 'sometimes|string|max:255|unique:' . config('extension.tables.plugin') . ',name,' . $plugin->id . ',id,extension_id,' . $plugin->extension_id;
        }

        if (!empty($this->fields)) {
            $rules['fields'] = 'array|sometimes';

            foreach ($this->fields as $key => $field) {
                if (empty($field['validation'])) {
                    $field['validation'] = 'string|nullable|sometimes';
                } else if (is_string($field['validation'])) {
                    $field['validation'] = 'sometimes|' . $field['validation'];
                } else {
                    $field['validation'] = array_merge(['sometimes'], $field['validation']);
                }

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
     * Set the plugin.
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
