<?php

namespace JobMetric\Extension\Http\Resources\Fields;

use Illuminate\Http\Request;

/**
 * Class BooleanFieldResource
 *
 * Transforms a boolean field into a structured JSON resource.
 * Extends the base FieldResource with boolean-specific properties.
 *
 * @property bool|null $value  The current boolean value of the field.
 */
class BooleanFieldResource extends FieldResource
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
            'value' => $this['value'] ?? null,
        ]);
    }
}
