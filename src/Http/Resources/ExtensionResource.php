<?php

namespace JobMetric\Extension\Http\Resources;

use Carbon\Carbon;
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
            'installed_at' => isset($this['data']['created_at']) ? Carbon::make($this['data']['created_at'])->format('Y-m-d H:i:s') : '',
            'updated_at' => isset($this['data']['updated_at']) ? Carbon::make($this['data']['updated_at'])->format('Y-m-d H:i:s') : '',
            'plugin_count' => $this['data']['plugin_count'] ?? 0,
        ];

        if ($data['installed']) {
            if ($data['multiple']) {
                $data['plugins_link'] = route('extension.plugin.index', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $this['data']['id']
                ]);
                $data['plugin_add'] = route('extension.plugin.create', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $this['data']['id']
                ]);
            } else {
                $data['edit_link'] = route('extension.plugin.edit', [
                    'panel' => $request->panel,
                    'section' => $request->section,
                    'type' => $request->type,
                    'jm_extension' => $this['data']['id'],
                    'jm_plugin' => $this['data']['plugins'][0]['id']
                ]);
            }
        }

        return $data;
    }
}
