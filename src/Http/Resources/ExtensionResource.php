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
 * @property mixed plugin_count
 * @property mixed plugins
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
            'id' => $this['data']['id'] ?? null,
            'extension' => $this['extension'],
            'name' => $this['name'],
            'version' => $this['version'],
            'title' => trans($this['title']),
            'description' => trans($this['description']),
            'author' => $this['author'] ?? '',
            'email' => $this['email'] ?? '',
            'website' => $this['website'] ?? '',
            'creation_at' => $this['creationDate'] ?? '',
            'copyright' => $this['copyright'] ?? '',
            'license' => $this['license'] ?? '',
            'multiple' => $this['multiple'],
            'namespace' => $this['namespace'],
            'deletable' => $this['deletable'],
            'installed' => $this['installed'],
            'installed_at' => $this['data']['created_at'] ?? '',
            'updated_at' => $this['data']['updated_at'] ?? '',
            'plugin_count' => $this['data']['plugin_count'] ?? 0,
        ];
    }
}
