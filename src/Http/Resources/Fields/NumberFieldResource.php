<?php

namespace JobMetric\Extension\Http\Resources\Fields;

use Illuminate\Http\Request;

/**
 * Class NumberFieldResource
 *
 * Transforms a number field into a structured JSON resource.
 * Extends the base FieldResource with number-specific properties.
 *
 * @property string $extension       The extension type/category.
 * @property string $extension_name  The extension name.
 * @property string $name            The field name identifier.
 * @property string|null $placeholder The placeholder text for the input.
 * @property int|float|null $value   The current numeric value of the field.
 */
class NumberFieldResource extends FieldResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge($this->common(), [
            'placeholder' => trans("extension::base.fields.{$this['name']}.placeholder")
                ?? trans("extension_{$this['extension']}_{$this['extension_name']}::extension.fields.{$this['name']}.placeholder")
                ?? $this['name'],
            'value' => $this['value'] ?? null,
        ]);
    }
}
