<?php

namespace JobMetric\Extension\Support;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use JobMetric\Extension\Models\Extension as ExtensionModel;

/**
 * Reads and writes the installed extensions cache file (storage/app/installed_extensions.json).
 * Used by ExtensionKernel to avoid querying the extensions table on every request.
 *
 * @package JobMetric\Extension
 */
class InstalledExtensionsFile
{
    /**
     * Get the absolute path to the installed extensions JSON file.
     *
     * @return string
     */
    public function path(): string
    {
        return storage_path('app/installed_extensions.json');
    }

    /**
     * Read installed extension namespaces from the file.
     * Returns empty array if file is missing or invalid.
     *
     * @return array<int, string> List of extension class FQCNs.
     * @throws FileNotFoundException
     */
    public function read(): array
    {
        $path = $this->path();
        if (! is_file($path)) {
            return [];
        }

        $json = File::get($path);
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }

        $namespaces = [];
        foreach ($data as $extension) {
            if (is_array($extension) && isset($extension['namespace']) && is_string($extension['namespace'])) {
                $namespaces[] = $extension['namespace'];
            }
        }

        return $namespaces;
    }

    /**
     * Build file content from the database and write to storage.
     * Call after install/uninstall so the file stays in sync.
     * Each item in the file has: id, extension, name, namespace, info, created_at, updated_at.
     *
     * @param Application $app Application instance (used to check if db is bound).
     *
     * @return void
     */
    public function syncFromDatabase(Application $app): void
    {
        if (! $app->bound('db')) {
            return;
        }

        $items = ExtensionModel::orderBy('extension')->orderBy('name')->get()->map(function (ExtensionModel $model) {
            return [
                'id'         => $model->id,
                'extension'  => $model->extension,
                'name'       => $model->name,
                'namespace'  => $model->namespace,
                'info'       => $model->info !== null ? (array) $model->info : null,
                'created_at' => $model->created_at?->toIso8601String(),
                'updated_at' => $model->updated_at?->toIso8601String(),
            ];
        })->all();

        $content = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::put($this->path(), $content);
    }
}
