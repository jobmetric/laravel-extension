<?php

namespace JobMetric\Extension\Http\Resources\Fields;

use Illuminate\Http\Request;

/**
 * @property mixed value
 */
class BooleanFieldResource extends FieldResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge($this->common(), [
            'value' => $this['value'] ?? null,
        ]);
    }
}
