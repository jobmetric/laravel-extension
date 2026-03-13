<?php

namespace JobMetric\Extension\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Extension\Models\Plugin;

/**
 * Class ExtensionResource
 *
 * Transforms the Extension model or an array spec (from list) into a structured JSON resource.
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
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $info = $this['info'] ?? [];
        $data = [
            'id' => $this['data']['id'] ?? $this->resource?->getKey(),
            'extension' => $this['extension'],
            'name' => $this['name'],
            'version' => $this['version'] ?? $info['version'] ?? null,
            'title' => isset($this['title']) ? trans($this['title']) : trans($info['title'] ?? ''),
            'description' => isset($this['description']) ? trans($this['description']) : trans($info['description'] ?? ''),
            'author' => $this['author'] ?? $info['author'] ?? '',
            'email' => $this['email'] ?? $info['email'] ?? '',
            'website' => $this['website'] ?? $info['website'] ?? '',
            'creation_at' => $this['creationDate'] ?? $info['creationDate'] ?? '',
            'copyright' => $this['copyright'] ?? $info['copyright'] ?? '',
            'license' => $this['license'] ?? $info['license'] ?? '',
            'multiple' => (bool) ($this['multiple'] ?? ($info['multiple'] ?? false)),
            'namespace' => $this['namespace'],
            'deletable' => (bool) ($this['deletable'] ?? false),
            'installed' => (bool) ($this['installed'] ?? false),
            'needs_update' => (bool) ($this['needs_update'] ?? false),

            // ISO 8601 timestamps for interoperability across clients
            'installed_at' => isset($this['data']['created_at'])
                ? Carbon::make($this['data']['created_at'])?->toISOString()
                : (isset($this['created_at']) ? Carbon::make($this['created_at'])?->toISOString() : null),
            'updated_at' => isset($this['data']['updated_at'])
                ? Carbon::make($this['data']['updated_at'])?->toISOString()
                : (isset($this['updated_at']) ? Carbon::make($this['updated_at'])?->toISOString() : null),

            'plugins_count' => $this['data']['plugins_count'] ?? $this['plugins_count'] ?? 0,

            // Loaded relations
            'plugins' => $this->whenLoaded('plugins', function () {
                return PluginResource::collection($this->plugins);
            }),
        ];

        $extensionId = $this['data']['id'] ?? $this->resource?->getKey();
        if ($data['installed'] && $extensionId !== null) {
            if ($data['multiple']) {
                $data['plugins_link'] = route('extension.plugin.index', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $extensionId,
                ]);
                $data['plugin_add'] = route('extension.plugin.create', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $extensionId,
                ]);
            } else {
                $pluginId = $this['data']['plugins'][0]['id'] ?? $this->resource?->plugins?->first()?->getKey();
                if ($pluginId !== null) {
                    $data['edit_link'] = route('extension.plugin.edit', [
                        'panel' => $request->panel,
                        'section' => $request->section,
                        'type' => $request->type,
                        'jm_extension' => $extensionId,
                        'jm_plugin' => $pluginId,
                    ]);
                }
            }
        }

        return $data;
    }
}
