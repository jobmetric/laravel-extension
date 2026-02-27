<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Commands\Tools\ToolRegistry;

class ExtensionMakeTools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:make-tools
        {extension? : Extension type (e.g. Module)}
        {name? : Extension name (e.g. Banner)}
        {tool? : Tool type (e.g. model, controller, migration)}
        {target? : Target name (e.g. Post for model)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Laravel-style artifacts (model, controller, etc.) inside an extension';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $extension = $this->resolveArgument($this->argument('extension'), fn () => $this->askExtension());
        if ($extension === null) {
            return 1;
        }

        $extension = Str::studly(trim($extension));

        $name = $this->resolveArgument($this->argument('name'), fn () => $this->askName($extension));
        if ($name === null) {
            return 1;
        }

        $name = Str::studly(trim($name));

        $tool = $this->resolveArgument($this->argument('tool'), fn () => $this->askTool());
        $target = $this->resolveArgument($this->argument('target'), fn () => $this->askTarget($tool ?? ''));

        if ($tool === null || $target === null) {
            return 1;
        }

        $tool = strtolower(trim($tool));

        $target = trim($target);
        if ($target === '') {
            $this->error('Target name is required.');

            return 1;
        }

        $path = $this->extensionPath($extension, $name);
        if (! File::isDirectory($path)) {
            $this->error('Extension does not exist: ' . $extension . '/' . $name . ' at ' . $path);

            return 2;
        }

        $config = ToolRegistry::get($tool);
        if ($config === null) {
            $this->error('Unknown tool: ' . $tool . '. Available: ' . implode(', ', array_keys(ToolRegistry::all())));

            return 3;
        }

        $options = $this->resolveToolOptions($tool, $config['options']);

        $options['tool'] = $tool;
        $options['extension'] = $extension;
        $options['name'] = $name;
        $options['target'] = $target;

        $generatorClass = $config['generator'];
        $generator = new $generatorClass();
        $namespace = $this->coreNamespace($extension, $name);

        return $generator->generate($path, $namespace, $options, $this);
    }

    /**
     * Resolve an argument value, using the provided ask callback if the value is not given or empty.
     * Returns null if no value is provided and the user does not input a value when prompted.
     *
     * @param mixed $value
     * @param callable $ask
     *
     * @return string|null
     */
    private function resolveArgument(mixed $value, callable $ask): ?string
    {
        $v = $value !== null && trim((string) $value) !== '' ? trim((string) $value) : null;
        if ($v !== null) {
            return $v;
        }

        if ($this->input->isInteractive()) {
            return $ask();
        }

        return null;
    }

    /**
     * Extension types that have at least one extension (subdir with extension.json).
     *
     * @return array<int, string>
     */
    private function getExistingExtensionTypes(): array
    {
        $base = $this->extensionBasePath();
        if (! File::isDirectory($base)) {
            return [];
        }

        $types = [];
        foreach (File::directories($base) as $typeDir) {
            $type = basename($typeDir);
            foreach (File::directories($typeDir) as $nameDir) {
                if (File::isFile($nameDir . DIRECTORY_SEPARATOR . 'extension.json')) {
                    $types[] = $type;
                    break;
                }
            }
        }

        sort($types);

        return $types;
    }

    /**
     * Names of extensions under the given type (dirs that contain extension.json).
     *
     * @return array<int, string>
     */
    private function getExistingExtensionNames(string $extension): array
    {
        $base = $this->extensionBasePath();
        $typeDir = $base . DIRECTORY_SEPARATOR . $extension;
        if (! File::isDirectory($typeDir)) {
            return [];
        }

        $names = [];
        foreach (File::directories($typeDir) as $nameDir) {
            $name = basename($nameDir);
            if (File::isFile($nameDir . DIRECTORY_SEPARATOR . 'extension.json')) {
                $names[] = $name;
            }
        }

        sort($names);

        return $names;
    }

    /**
     * Ask user to select an extension type from existing extensions, or input a new one if none exist.
     *
     * @return string|null
     */
    private function askExtension(): ?string
    {
        $types = $this->getExistingExtensionTypes();
        if ($types === []) {
            $this->warn('No existing extensions found. Create an extension first with extension:make.');

            return $this->ask('Extension type (e.g. Module)');
        }

        return $this->choice('Select extension type', $types);
    }

    /**
     * Ask user to select an extension name from existing extensions of the given type.
     *
     * @param string $extension
     *
     * @return string|null
     */
    private function askName(string $extension): ?string
    {
        $names = $this->getExistingExtensionNames($extension);
        if ($names === []) {
            $this->error('No extensions found for type: ' . $extension);

            return null;
        }

        return $this->choice('Select extension name', $names);
    }

    /**
     * Base path to the Extensions directory (e.g. app/Extensions), where extension types are subdirectories.
     *
     * @return string
     */
    private function extensionBasePath(): string
    {
        $appDir = function_exists('appFolderName') ? appFolderName() : 'app';

        return base_path($appDir . DIRECTORY_SEPARATOR . 'Extensions');
    }

    /**
     * Ask user to select a tool type from the registry.
     *
     * @return string|null
     */
    private function askTool(): ?string
    {
        $choices = ToolRegistry::choices();

        return $this->choice('Tool type', array_keys($choices));
    }

    /**
     * Ask user for the target name of the tool (e.g. model name for a model tool).
     *
     * @param string $tool
     *
     * @return string|null
     */
    private function askTarget(string $tool): ?string
    {
        $prompt = $tool === 'model' ? 'Model name (e.g. Post)' : 'Name (e.g. Post)';
        $answer = $this->ask($prompt);

        return $answer !== null && trim($answer) !== '' ? trim($answer) : null;
    }

    /**
     * Ask user for any additional options for the tool, based on the tool's option definitions.
     *
     * @param array<int, array{key: string, question: string, default: bool|string|null, type: string}> $optionDefs
     *
     * @return array<string, mixed>
     */
    private function resolveToolOptions(string $tool, array $optionDefs): array
    {
        $options = [];
        $allTriggered = false;

        foreach ($optionDefs as $def) {
            $key = $def['key'];
            $default = $def['default'] ?? false;
            $type = $def['type'] ?? 'bool';

            if ($this->input->isInteractive()) {
                $value = $type === 'string' ? $this->ask($def['question'], $default === null ? '' : (string) $default) : $this->confirm($def['question'], (bool) $default);
                if ($type === 'string' && ($value === null || trim((string) $value) === '')) {
                    $value = $default;
                }
            }
            else {
                $value = $default;
            }

            $options[$key] = $value;

            if ($key === 'all' && $value === true) {
                $allTriggered = true;
                break;
            }
        }

        if ($allTriggered && $tool === 'model') {
            $options['migration'] = true;
            $options['factory'] = true;
            $options['seed'] = true;
            $options['policy'] = true;
            $options['controller'] = true;
            $options['resource'] = true;
            $options['requests'] = true;
        }

        return $options;
    }

    /**
     * Get the base path for the given extension type and name (e.g. app/Extensions/Module/Banner).
     *
     * @param string $extension
     * @param string $name
     *
     * @return string
     */
    private function extensionPath(string $extension, string $name): string
    {
        $appDir = function_exists('appFolderName') ? appFolderName() : 'app';

        return base_path($appDir . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * Get the base namespace for the given extension type and name (e.g. App\Extensions\Module\Banner\Core).
     *
     * @param string $extension
     * @param string $name
     *
     * @return string
     */
    private function coreNamespace(string $extension, string $name): string
    {
        $root = function_exists('appNamespace') ? trim(appNamespace(), '\\') : 'App';

        return $root . '\\Extensions\\' . $extension . '\\' . $name . '\\Core';
    }
}
