<?php

namespace JobMetric\Extension\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Extension\Models\Extension;

/**
 * @property int $id
 * @property int $extension_id
 * @property Extension $extension
 * @property string $name
 * @property array $fields
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PluginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'extension' => $this->whenLoaded('extension', ExtensionResource::make($this->extension)),
            'name' => trans($this->name),
            'fields' => $this->fields,
            'status' => $this->status,
            'created_at' => Carbon::make($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::make($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
