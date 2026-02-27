<?php

namespace JobMetric\Extension\Commands\Tools\Generators;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Commands\Tools\Contracts\ExtensionToolGeneratorInterface;

class ControllerGenerator extends AbstractExtensionToolGenerator implements ExtensionToolGeneratorInterface
{
    /**
     * Generate a controller class for the extension.
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
        $target = $options['target'] ?? '';
        $extension = $options['extension'] ?? '';
        $name = $options['name'] ?? '';
        if ($target === '') {
            $command->error('Controller name is required.');
            return 1;
        }

        $class = Str::studly($target);
        $className = str_ends_with($class, 'Controller') ? $class : $class . 'Controller';
        $coreNs = $this->coreNamespace($extension, $name);
        $controllerNs = $coreNs . '\\Http\\Controllers';
        $force = (bool) ($options['force'] ?? false);

        $controllerDir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers';
        $filePath = $controllerDir . DIRECTORY_SEPARATOR . $className . '.php';

        if (File::isFile($filePath) && ! $force) {
            $command->warn('Controller already exists. Use --force to overwrite.');
            return 2;
        }

        $invokable = (bool) ($options['invokable'] ?? false);
        $resource = (bool) ($options['resource'] ?? false);
        $api = (bool) ($options['api'] ?? false);
        $requests = (bool) ($options['requests'] ?? false);

        if ($invokable) {
            $stubName = 'controller.invokable.stub';
        } elseif ($resource || $api) {
            $stubName = $api ? 'controller.api.stub' : 'controller.resource.web.stub';
        } else {
            $stubName = 'controller.plain.stub';
        }

        $baseName = str_replace('Controller', '', $className);
        $requestNs = $coreNs . '\\Http\\Requests';
        $replace = [
            'namespace'           => $controllerNs,
            'class'               => $className,
            'requestImports'      => '',
            'storeRequestClass'   => 'Request',
            'updateRequestClass'  => 'Request',
        ];

        if ($requests && ($resource || $api)) {
            $replace['requestImports'] = 'use ' . $requestNs . '\\Store' . $baseName . 'Request;' . "\n" . 'use ' . $requestNs . '\\Update' . $baseName . 'Request;';
            $replace['storeRequestClass'] = 'Store' . $baseName . 'Request';
            $replace['updateRequestClass'] = 'Update' . $baseName . 'Request';
        }
        $written = $this->writeStub($filePath, $stubName, $replace, $command);
        if (! $written) {
            return 3;
        }

        if ($requests && ($resource || $api)) {
            $requestDir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests';
            $baseName = str_replace('Controller', '', $className);
            foreach (['Store', 'Update'] as $prefix) {
                $reqClass = $prefix . $baseName . 'Request';
                $reqPath = $requestDir . DIRECTORY_SEPARATOR . $reqClass . '.php';
                $this->writeStub($reqPath, 'request.stub', [
                    'namespace' => $coreNs . '\\Http\\Requests',
                    'class'     => $reqClass,
                ], $command);
            }
        }

        if (! empty($options['test'])) {
            $this->writeControllerTest($basePath, $coreNs, $controllerNs, $className, $force, $command);
        }

        return 0;
    }

    /**
     * Write a test class for the generated controller.
     *
     * @param string $basePath
     * @param string $coreNs
     * @param string $controllerNs
     * @param string $className
     * @param bool $force
     * @param Command $command
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeControllerTest(
        string $basePath,
        string $coreNs,
        string $controllerNs,
        string $className,
        bool $force,
        Command $command
    ): void {
        $testDir = $basePath . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers';
        $testClass = $className . 'Test';
        $testPath = $testDir . DIRECTORY_SEPARATOR . $testClass . '.php';

        if (File::isFile($testPath) && ! $force) {
            $command->warn('Test file already exists. Use --force to overwrite.');
            return;
        }

        $extensionNs = str_replace('\\Core', '', $coreNs);
        $replace = [
            'namespace'      => $extensionNs . '\\Tests\\Http\\Controllers',
            'class'          => $testClass,
            'controllerFqcn' => $controllerNs . '\\' . $className,
        ];

        $content = $this->getStub('controller.test.stub', $replace);
        if ($content === '') {
            $command->warn('Controller test stub not found. Add test manually.');
            return;
        }

        File::ensureDirectoryExists($testDir);
        File::put($testPath, $content);

        $command->info('Created: ' . $testPath);
    }
}
