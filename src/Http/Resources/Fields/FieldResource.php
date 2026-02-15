<?php

namespace JobMetric\Extension\Http\Resources\Fields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class FieldResource
 *
 * Base resource class for field transformations.
 * Provides common field properties used by all field types.
 *
 * @property string $extension       The extension type/category.
 * @property string $extension_name  The extension name.
 * @property string $name            The field name identifier.
 * @property string $type            The field type (text, number, boolean, etc.).
 * @property string|null $label      The display label for the field.
 * @property bool $required          Whether the field is required.
 * @property mixed $default          The default value for the field.
 * @property string|null $info       Additional information/help text for the field.
 */
class FieldResource extends JsonResource
{
    /**
     * Get common field properties shared by all field types.
     *
     * @return array<string, mixed>
     */
    public function common(): array
    {
        return [
            'name' => $this['name'],
            'type' => $this['type'],
            'required' => $this['required'] ?? false,
            'default' => $this['default'] ?? null,
            'label' => trans("extension::base.fields.{$this['name']}.label")
                ?? trans("extension_{$this['extension']}_{$this['extension_name']}::extension.fields.{$this['name']}.label")
                ?? $this['label']
                ?? $this['name'],
            'info' => trans("extension::base.fields.{$this['name']}.info")
                ?? trans("extension_{$this['extension']}_{$this['extension_name']}::extension.fields.{$this['name']}.info")
                ?? $this['info']
                ?? $this['name'],
        ];
    }
}
