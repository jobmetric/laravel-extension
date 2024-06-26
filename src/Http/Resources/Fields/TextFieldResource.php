<?php

namespace JobMetric\Extension\Http\Resources\Fields;

use Illuminate\Http\Request;

/**
 * @property mixed extension
 * @property mixed extension_name
 * @property mixed name
 * @property mixed placeholder
 * @property mixed value
 */
class TextFieldResource extends FieldResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $placeholder = trans("extension::base.fields.{$this['name']}.placeholder") ?? trans("extension::{$this['extension']}.{$this['extension_name']}.fields.{$this['name']}.placeholder") ?? $this['name'];

        return array_merge($this->common(), [
            'placeholder' => $placeholder,
            'value' => $this['value'] ?? null,
        ]);
    }
}
