<?php

namespace JobMetric\Extension\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Extension\Models\Plugin;

/**
 * Class ExtensionResource
 *
 * Transforms the Extension model into a structured JSON resource.
 *
 * @property int $id
 * @property string $extension
 * @property string $name
 * @property string $namespace
 * @property array|null $info
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Plugin[] $plugins
 * @property-read int $plugins_count
 */
class ExtensionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
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
            'installed' => $this['installed'] ?? false,

            // ISO 8601 timestamps for interoperability across clients
            'installed_at' => isset($this['data']['created_at'])
                ? Carbon::make($this['data']['created_at'])?->toISOString()
                : null,
            'updated_at' => isset($this['data']['updated_at'])
                ? Carbon::make($this['data']['updated_at'])?->toISOString()
                : null,

            'plugins_count' => $this['data']['plugins_count'] ?? 0,
        ];

        if ($data['installed']) {
            if ($data['multiple']) {
                $data['plugins_link'] = route('extension.plugin.index', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $this['data']['id'],
                ]);
                $data['plugin_add'] = route('extension.plugin.create', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $this['data']['id'],
                ]);
            } else {
                $data['edit_link'] = route('extension.plugin.edit', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $this['data']['id'],
                    'jm_plugin' => $this['data']['plugins'][0]['id'],
                ]);
            }
        }

        return $data;
    }
}
