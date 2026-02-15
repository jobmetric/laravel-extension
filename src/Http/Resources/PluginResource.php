<?php

namespace JobMetric\Extension\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Extension\Models\Extension;

/**
 * Class PluginResource
 *
 * Transforms the Plugin model into a structured JSON resource.
 *
 * @property int $id
 * @property int $extension_id
 * @property string $name
 * @property array|null $fields
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Extension $extension
 */
class PluginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'extension_id' => $this->extension_id,

            // Loaded relations
            'extension' => $this->whenLoaded('extension', function () {
                return ExtensionResource::make($this->extension);
            }),

            'name' => trans($this->name),
            'fields' => $this->fields,
            'status' => (bool) $this->status,

            // ISO 8601 timestamps for interoperability across clients
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
