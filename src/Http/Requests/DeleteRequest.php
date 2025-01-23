<?php

namespace JobMetric\Extension\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Extension\Rules\ExtensionExistRule;

class DeleteRequest extends FormRequest
{
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
        $parameters = request()->route()->parameters();
        $type = $parameters['type'];

        return [
            'namespace' => [
                'required',
                'string',
                'max:255',
                new ExtensionExistRule($type),
            ],
        ];
    }
}
