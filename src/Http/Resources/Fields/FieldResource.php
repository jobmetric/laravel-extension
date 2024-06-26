<?php

namespace JobMetric\Extension\Http\Resources\Fields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed extension
 * @property mixed extension_name
 * @property mixed name
 * @property mixed type
 * @property mixed label
 * @property mixed required
 * @property mixed default
 * @property mixed info
 */
class FieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
            'label' => trans("extension::base.fields.{$this['name']}.label") ?? trans("extension::{$this['extension']}.{$this['extension_name']}.fields.{$this['name']}.label") ?? $this['label'] ?? $this['name'],
            'info' => trans("extension::base.fields.{$this['name']}.info") ?? trans("extension::{$this['extension']}.{$this['extension_name']}.fields.{$this['name']}.info") ?? $this['info'] ?? $this['name'],
        ];
    }
}
