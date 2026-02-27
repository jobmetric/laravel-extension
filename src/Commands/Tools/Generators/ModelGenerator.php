<?php

namespace JobMetric\Extension\Commands\Tools\Generators;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Commands\Tools\Contracts\ExtensionToolGeneratorInterface;

class ModelGenerator extends AbstractExtensionToolGenerator implements ExtensionToolGeneratorInterface
{
    /**
     * Generate a model and related files based on options.
     *
     * @param string $basePath
     * @param string $namespace
     * @param array<string, mixed> $options Must include 'target' (e.g. Post), 'extension', 'name'; plus tool options.
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
            $command->error('Target name (e.g. model name) is required.');

            return 1;
        }

        $class = Str::studly($target);
        $table = Str::snake(Str::plural($class));
        $coreNs = $this->coreNamespace($extension, $name);
        $force = (bool) ($options['force'] ?? false);

        $modelPath = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $class . '.php';
        if (File::isFile($modelPath) && ! $force) {
            $command->warn('Model already exists. Use --force to overwrite.');

            return 2;
        }

        $all = (bool) ($options['all'] ?? false);
        $migration = $all || (bool) ($options['migration'] ?? false);
        $factory = $all || (bool) ($options['factory'] ?? false);
        $seed = $all || (bool) ($options['seed'] ?? false);
        $controller = $all || (bool) ($options['controller'] ?? false);
        $policy = $all || (bool) ($options['policy'] ?? false);
        $resource = $all || (bool) ($options['resource'] ?? false);
        $api = (bool) ($options['api'] ?? false);
        $requests = $all || (bool) ($options['requests'] ?? false);
        $test = $all || (bool) ($options['test'] ?? false);
        $pivot = (bool) ($options['pivot'] ?? false);
        $morphPivot = (bool) ($options['morph-pivot'] ?? false);

        if ($controller && ! $resource && ! $api) {
            $resource = true;
        }

        $modelNamespace = $coreNs . '\\Models';
        $newFactory = '';
        if ($factory) {
            $factoryClass = $coreNs . '\\Factories\\' . $class . 'Factory';
            $newFactory = "\n\n    protected static function newFactory()\n    {\n        return \\" . $factoryClass . "::new();\n    }";
        }

        $replace = [
            'namespace'  => $modelNamespace,
            'class'      => $class,
            'table'      => $table,
            'newFactory' => $newFactory,
        ];

        if ($pivot || $morphPivot) {
            $this->writeStub($modelPath, 'model.pivot.stub', [
                'namespace' => $modelNamespace,
                'class'     => $class,
            ], $command);
        }
        else {
            $this->writeStub($modelPath, 'model.stub', $replace, $command);
        }

        if ($migration) {
            $migrationDir = $basePath . DIRECTORY_SEPARATOR . 'migrations';
            File::ensureDirectoryExists($migrationDir);
            $date = date('Y_m_d_His');
            $versionSlug = $this->getExtensionVersionSlug($basePath);
            $migrationName = 'create_' . $table . '_table';
            $basename = $date . '__' . $versionSlug . '__' . $migrationName . '.php';
            $this->writeStub($migrationDir . DIRECTORY_SEPARATOR . $basename, 'model.migration.stub', ['table' => $table], $command);
        }

        if ($factory) {
            $factoryDir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Factories';
            $factoryReplace = [
                'namespace'  => $coreNs . '\\Factories',
                'class'      => $class,
                'modelClass' => $modelNamespace . '\\' . $class,
                'modelShort' => $class,
            ];
            $this->writeStub($factoryDir . DIRECTORY_SEPARATOR . $class . 'Factory.php', 'model.factory.stub', $factoryReplace, $command);
        }

        if ($seed) {
            $extensionNs = str_replace('\\Core', '', $coreNs);
            $seederDir = $basePath . DIRECTORY_SEPARATOR . 'Seeders';
            $this->writeStub($seederDir . DIRECTORY_SEPARATOR . $class . 'Seeder.php', 'model.seeder.stub', [
                'namespace' => $extensionNs . '\\Seeders',
                'class'     => $class,
            ], $command);
        }

        if ($policy) {
            $policyDir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Policies';
            $this->writeStub($policyDir . DIRECTORY_SEPARATOR . $class . 'Policy.php', 'model.policy.stub', [
                'namespace'  => $coreNs . '\\Policies',
                'class'      => $class,
                'modelClass' => $modelNamespace . '\\' . $class,
                'modelShort' => $class,
            ], $command);
        }

        if ($controller) {
            $controllerDir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers';
            $controllerStub = $api ? 'controller.resource.stub' : 'controller.resource.web.stub';
            $controllerClass = $class . 'Controller';
            $requestNs = $coreNs . '\\Http\\Requests';
            $controllerReplace = [
                'namespace'          => $coreNs . '\\Http\\Controllers',
                'class'              => $controllerClass,
                'requestImports'     => '',
                'storeRequestClass'  => 'Request',
                'updateRequestClass' => 'Request',
            ];
            if ($requests) {
                $controllerReplace['requestImports'] = 'use ' . $requestNs . '\\Store' . $class . 'Request;' . "\n" . 'use ' . $requestNs . '\\Update' . $class . 'Request;';
                $controllerReplace['storeRequestClass'] = 'Store' . $class . 'Request';
                $controllerReplace['updateRequestClass'] = 'Update' . $class . 'Request';
            }
            $this->writeStub($controllerDir . DIRECTORY_SEPARATOR . $controllerClass . '.php', $controllerStub, $controllerReplace, $command);
        }

        if ($requests && $controller) {
            $requestDir = $basePath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests';
            foreach (['Store', 'Update'] as $prefix) {
                $this->writeStub($requestDir . DIRECTORY_SEPARATOR . $prefix . $class . 'Request.php', 'request.stub', [
                    'namespace' => $coreNs . '\\Http\\Requests',
                    'class'     => $prefix . $class . 'Request',
                ], $command);
            }
        }

        if ($test) {
            $this->writeModelTest($basePath, $coreNs, $class, $modelNamespace, $force, $command);
        }

        return 0;
    }

    /**
     * Write a PHPUnit test class for the model.
     *
     * @param string $basePath
     * @param string $coreNs
     * @param string $modelClass
     * @param string $modelNamespace
     * @param bool $force
     * @param Command $command
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeModelTest(
        string $basePath,
        string $coreNs,
        string $modelClass,
        string $modelNamespace,
        bool $force,
        Command $command
    ): void {
        $testDir = $basePath . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'Models';
        $testClass = $modelClass . 'Test';
        $testPath = $testDir . DIRECTORY_SEPARATOR . $testClass . '.php';

        if (File::isFile($testPath) && ! $force) {
            $command->warn('Test file already exists. Use --force to overwrite.');

            return;
        }

        $extensionNs = str_replace('\\Core', '', $coreNs);
        $replace = [
            'namespace' => $extensionNs . '\\Tests\\Models',
            'class'     => $testClass,
            'modelFqcn' => $modelNamespace . '\\' . $modelClass,
        ];

        $content = $this->getStub('model.test.stub', $replace);
        if ($content === '') {
            $command->warn('Model test stub not found. Add test manually.');

            return;
        }

        File::ensureDirectoryExists($testDir);
        File::put($testPath, $content);

        $command->info('Created: ' . $testPath);
    }
}
