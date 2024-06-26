<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationProvince;
use JobMetric\Location\Rules\CheckExistNameRule;

class PluginRequest extends FormRequest
{
    public array $fields = [];

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
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'string|min:3|max:255',
            'status' => 'boolean',
        ];

        if (!empty($this->fields)) {
            $rules['fields'] = 'array';

            foreach ($this->fields as $key => $validation) {
                $rules['fields.' . $key] = $validation;
            }
        }

        return $rules;
    }

    /**
     * Set fields for validation
     *
     * @param array $fields
     * @return static
     */
    public function setFields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }
}
