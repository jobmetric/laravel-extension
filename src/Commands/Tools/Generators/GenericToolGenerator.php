<?php

namespace JobMetric\Extension\Commands\Tools\Generators;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Commands\Tools\Contracts\ExtensionToolGeneratorInterface;
use JobMetric\Extension\Commands\Tools\ToolRegistry;

/**
 * Generic single-class generator for extension make-tools (cast, channel, command, etc.).
 * Uses ToolRegistry entry: stub, subfolder. Options must include: tool, target, extension, name.
 */
class GenericToolGenerator extends AbstractExtensionToolGenerator implements ExtensionToolGeneratorInterface
{
    /**
     * Generate a class file for the specified tool and target, using the configured stub and subfolder.
     *
     * @param string $basePath
     * @param string $namespace
     * @param array $options
     * @param Command $command
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function generate(string $basePath, string $namespace, array $options, Command $command): int
    {
        $tool = $options['tool'] ?? '';
        $target = $options['target'] ?? '';
        $extension = $options['extension'] ?? '';
        $name = $options['name'] ?? '';
        if ($tool === '' || $target === '') {
            $command->error('Options "tool" and "target" are required.');

            return 1;
        }

        $config = ToolRegistry::get($tool);
        if ($config === null || empty($config['stub']) || empty($config['subfolder'])) {
            $command->error('Tool "' . $tool . '" is not configured for GenericToolGenerator (missing stub or subfolder).');

            return 2;
        }

        $class = Str::studly($target);
        if ($tool === 'exception' && ! str_ends_with($class, 'Exception')) {
            $class .= 'Exception';
        }

        if ($tool === 'request' && ! str_ends_with($class, 'Request')) {
            $class .= 'Request';
        }

        if ($tool === 'resource' && ! str_ends_with($class, 'Resource')) {
            $class .= 'Resource';
        }

        $subfolder = $config['subfolder'];
        $stubName = $this->resolveStubName($config, $options);
        $force = (bool) ($options['force'] ?? false);

        $coreNs = $this->coreNamespace($extension, $name);
        if ($tool === 'test') {
            $extensionNs = str_replace('\\Core', '', $coreNs);
            $targetNamespace = $extensionNs . '\\' . str_replace('/', '\\', $subfolder);
            $dir = $basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $subfolder);
        }
        else if ($tool === 'seeder') {
            $extensionNs = str_replace('\\Core', '', $coreNs);
            $targetNamespace = $extensionNs . '\\Seeders';
            $dir = $basePath . DIRECTORY_SEPARATOR . 'Seeders';
        }
        else {
            $targetNamespace = $coreNs . '\\' . str_replace('/', '\\', $subfolder);
            $dir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $subfolder);
        }
        $fileName = ($tool === 'factory' && ! str_ends_with($class, 'Factory')) ? $class . 'Factory.php' : $class . '.php';
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

        if (File::isFile($filePath) && ! $force) {
            $command->warn('File already exists. Use --force to overwrite.');

            return 3;
        }

        $replace = [
            'namespace' => $targetNamespace,
            'class'     => $class,
        ];

        if ($tool === 'component') {
            $replace['view'] = Str::kebab($class);
            $replace['configKey'] = 'extension_' . Str::snake($options['extension'] ?? '') . '_' . Str::snake($options['name'] ?? '');
        }

        if ($tool === 'mail') {
            $replace['configKey'] = 'extension_' . Str::snake($options['extension'] ?? '') . '_' . Str::snake($options['name'] ?? '');
            $replace['viewPath'] = $replace['configKey'] . '::emails.' . Str::kebab($class);
        }

        $written = $this->writeStub($filePath, $stubName, $replace, $command);

        if ($written && $tool === 'component') {
            $this->writeComponentView($basePath, $replace['view'], $force, $command);
        }

        if ($written && $tool === 'mail') {
            $this->writeMailView($basePath, Str::kebab($class), $force, $command);
        }

        return $written ? 0 : 4;
    }

    /**
     * Write a Blade view file for a component, using a default stub if the configured one is missing. Skip if file exists and not forcing.
     *
     * @param string $basePath
     * @param string $viewName
     * @param bool $force
     * @param Command $command
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeComponentView(string $basePath, string $viewName, bool $force, Command $command): void
    {
        $viewDir = $basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'components';
        $viewPath = $viewDir . DIRECTORY_SEPARATOR . $viewName . '.blade.php';

        if (File::isFile($viewPath) && ! $force) {
            return;
        }

        $stubPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'component-view.blade.php.stub';
        $content = File::isFile($stubPath) ? File::get($stubPath) : "<div>\n    {{ \$slot }}\n</div>\n";

        File::ensureDirectoryExists($viewDir);
        File::put($viewPath, $content);
        $command->info('Created: ' . $viewPath);
    }

    /**
     * Write a Blade view file for a mail class, using a default stub if the configured one is missing. Skip if file exists and not forcing.
     *
     * @param string $basePath
     * @param string $viewName
     * @param bool $force
     * @param Command $command
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeMailView(string $basePath, string $viewName, bool $force, Command $command): void
    {
        $viewDir = $basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'emails';
        $viewPath = $viewDir . DIRECTORY_SEPARATOR . $viewName . '.blade.php';

        if (File::isFile($viewPath) && ! $force) {
            return;
        }

        $stubPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'mail-view.blade.php.stub';
        $content = File::isFile($stubPath) ? File::get($stubPath) : "<div>\n    <p>{{ \$slot ?? '' }}</p>\n</div>\n";

        File::ensureDirectoryExists($viewDir);
        File::put($viewPath, $content);
        $command->info('Created: ' . $viewPath);
    }

    /**
     * Resolve stub filename from config: use stubMap when option is set, else default stub.
     *
     * @param array{stub: string, stubMap?: array<string, string>} $config
     * @param array<string, mixed> $options
     */
    private function resolveStubName(array $config, array $options): string
    {
        $stubMap = $config['stubMap'] ?? [];
        $setKeys = [];
        foreach (array_keys($stubMap) as $optionKey) {
            if (str_contains($optionKey, ',')) {
                continue;
            }
            if (! empty($options[$optionKey])) {
                $setKeys[] = $optionKey;
            }
        }
        if ($setKeys !== []) {
            sort($setKeys);
            $combined = implode(',', $setKeys);
            if (isset($stubMap[$combined])) {
                return $stubMap[$combined];
            }
            foreach ($setKeys as $key) {
                if (isset($stubMap[$key])) {
                    return $stubMap[$key];
                }
            }
        }

        return $config['stub'];
    }
}
