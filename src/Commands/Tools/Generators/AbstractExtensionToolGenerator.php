<?php

namespace JobMetric\Extension\Commands\Tools\Generators;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

abstract class AbstractExtensionToolGenerator
{
    /**
     * Base path for stubs.
     *
     * @var string
     */
    protected string $stubBasePath;

    /**
     * Constructor to set the stub base path.
     */
    public function __construct()
    {
        $this->stubBasePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'tools';
    }

    /**
     * Get stub content with replacements.
     *
     * @param string $stubName
     * @param array<string, string> $replace
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function getStub(string $stubName, array $replace): string
    {
        $path = $this->stubBasePath . DIRECTORY_SEPARATOR . $stubName;
        if (! File::isFile($path)) {
            return '';
        }

        $content = File::get($path);
        foreach ($replace as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * Write stub content to destination.
     *
     * @param string $destination
     * @param string $stubName
     * @param array<string, string> $replace
     * @param Command $command
     *
     * @return bool True if written.
     * @throws FileNotFoundException
     */
    protected function writeStub(string $destination, string $stubName, array $replace, Command $command): bool
    {
        $content = $this->getStub($stubName, $replace);
        if ($content === '') {
            return false;
        }

        $dir = dirname($destination);
        if (! File::isDirectory($dir)) {
            File::ensureDirectoryExists($dir);
        }

        File::put($destination, $content);
        $command->info('Created: ' . $destination);

        return true;
    }

    /**
     * Resolve extension path (e.g. base_path('app/Extensions/ExtensionType/Name')).
     * Assumes extensions are under app/Extensions. Adjust if your structure differs.
     *
     * @param string $extension
     * @param string $name
     *
     * @return string
     */
    protected function extensionPath(string $extension, string $name): string
    {
        $appDir = function_exists('appFolderName') ? appFolderName() : 'app';

        return base_path($appDir . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * Resolve core namespace for extension (e.g. App\Extensions\ExtensionType\Name\Core).
     *
     * @param string $extension
     * @param string $name
     *
     * @return string
     */
    protected function coreNamespace(string $extension, string $name): string
    {
        $root = function_exists('appNamespace') ? trim(appNamespace(), '\\') : 'App';

        return $root . '\\Extensions\\' . $extension . '\\' . $name . '\\Core';
    }

    /**
     * Get extension version slug (e.g. 1_0_0) from extension.json. Defaults to 1_0_0 if not found.
     *
     * @param string $basePath
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function getExtensionVersionSlug(string $basePath): string
    {
        $jsonPath = $basePath . DIRECTORY_SEPARATOR . 'extension.json';
        if (! File::isFile($jsonPath)) {
            return '1_0_0';
        }

        $data = json_decode(File::get($jsonPath), true);
        $version = is_array($data) && isset($data['version']) ? (string) $data['version'] : '1.0.0';

        return str_replace('.', '_', $version);
    }
}
