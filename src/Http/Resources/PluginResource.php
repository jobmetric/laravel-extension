<?php

namespace JobMetric\Extension\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed extension_id
 * @property mixed extension
 * @property mixed title
 * @property mixed fields
 * @property mixed status
 * @property mixed created_at
 * @property mixed updated_at
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
            'title' => $this->title,
            'fields' => $this->fields,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
