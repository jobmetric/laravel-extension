<?php

namespace JobMetric\Extension\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed extension
 * @property mixed name
 * @property mixed info
 * @property mixed created_at
 * @property mixed updated_at
 */
class ExtensionResource extends JsonResource
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
            'extension' => $this->extension,
            'name' => $this->name,
            'info' => $this->info,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
