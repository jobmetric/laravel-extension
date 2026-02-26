<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Facades\ExtensionTypeRegistry;

class ExtensionMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:make
        {extension? : Extension type (e.g. Module)}
        {name? : Extension name (e.g. Banner)}
        {--m|multiple : Allow multiple plugin instances}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make extension for Laravel';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $extension = $this->argument('extension');
        $name = $this->argument('name');

        $extension = $extension !== null && trim((string) $extension) !== '' ? $extension : null;
        $name = $name !== null && trim((string) $name) !== '' ? $name : null;

        if ($extension === null && $name === null) {
            $extension = $this->askExtension();
            if ($extension === null) {
                return 1;
            }

            $name = $this->askName();
            if ($name === null) {
                return 1;
            }
        }
        else if ($extension === null) {
            $extension = $this->askExtension();
            if ($extension === null) {
                return 1;
            }
        }
        else if ($name === null) {
            $name = $this->askName();
            if ($name === null) {
                return 1;
            }
        }

        $extension = Str::studly(trim($extension));
        $name = Str::studly(trim($name));

        $path = base_path(appFolderName() . '/Extensions/' . $extension . '/' . $name);
        if (File::isDirectory($path)) {
            $this->error('Extension already exists: ' . $extension . '/' . $name);

            return 2;
        }

        if (! ExtensionTypeRegistry::has($extension)) {
            $this->error('Extension type not registered: ' . $extension . '. Register it in config extension.types.');

            return 3;
        }

        $multiple = (bool) $this->option('multiple');
        if (! $this->option('multiple') && $this->input->isInteractive()) {
            $multiple = $this->confirm('Multiple plugin instances?');
        }

        $hasConfig = ! $this->input->isInteractive() || $this->confirm('Has config (config.php)?', true);
        $hasTranslation = ! $this->input->isInteractive() || $this->confirm('Has translation (lang)?', true);
        $hasView = $this->input->isInteractive() && $this->confirm('Has views (resources/views)?');
        $hasRoute = $this->input->isInteractive() && $this->confirm('Has routes (routes/route.php)?');
        $hasAsset = $this->input->isInteractive() && $this->confirm('Has assets (assets)?');
        $hasComponent = $this->input->isInteractive() && $this->confirm('Has Blade component (View/Components)?');
        $hasConsoleKernel = $this->input->isInteractive() && $this->confirm('Has ConsoleKernel (schedule)?');

        $replace = [
            'extension' => $extension,
            'name'      => $name,
            'multiple'  => $multiple ? 'true' : 'false',
            'date'      => date('Y-m-d H:i:s'),
            'configKey' => 'extension_' . Str::snake($extension) . '_' . Str::snake($name),
            'nameSnake' => Str::snake($name),
        ];

        $chain = $this->buildConfigurationChain($hasConfig, $hasTranslation, $hasView, $hasRoute, $hasAsset, $hasComponent, $hasConsoleKernel);
        $replace['configurationChain'] = $chain;

        File::ensureDirectoryExists($path);

        $this->writeExtensionClass($path, $name, $replace);
        $this->writeExtensionJson($path, $replace);

        if ($hasConfig) {
            $this->writeStub($path . '/config.php', 'config.php.stub', $replace);
        }

        if ($hasTranslation) {
            $langPath = $path . '/lang/en';
            File::ensureDirectoryExists($langPath);
            $this->writeStub($langPath . '/extension.php', 'lang-extension.php.stub', $replace);
        }

        if ($hasView) {
            $viewPath = $path . '/resources/views';
            File::ensureDirectoryExists($viewPath);
            $this->writeStub($viewPath . '/sample.blade.php', 'view.blade.php.stub', $replace);
        }

        if ($hasRoute) {
            $routeDir = $path . '/routes';
            File::ensureDirectoryExists($routeDir);
            $this->writeStub($routeDir . '/route.php', 'route.php.stub', $replace);
        }

        if ($hasAsset) {
            File::ensureDirectoryExists($path . '/assets');
            File::put($path . '/assets/.gitkeep', '');
        }

        if ($hasComponent) {
            $componentDir = $path . '/View/Components';
            File::ensureDirectoryExists($componentDir);
            $this->writeStub($componentDir . '/' . $name . 'Component.php', 'Component.php.stub', $replace);
            $viewDir = $path . '/resources/views/components';
            File::ensureDirectoryExists($viewDir);
            $this->writeStub($viewDir . '/' . Str::snake($name) . '-component.blade.php', 'component-view.blade.php.stub', $replace);
        }

        if ($hasConsoleKernel) {
            $this->writeStub($path . '/ConsoleKernel.php', 'ConsoleKernel.php.stub', $replace);
        }

        $this->info('Extension [' . $extension . '/' . $name . '] created successfully.');

        return 0;
    }

    /**
     * Ask the user to select an extension type from the config.
     * If no types are defined in the config, ask for a free-form extension type.
     *
     * @return string|null
     */
    private function askExtension(): ?string
    {
        $types = array_keys(config('extension.types', []));
        if ($types === []) {
            $this->warn('No extension types in config. Add extension.types (e.g. Module).');

            return $this->ask('Extension type (e.g. Module)');
        }

        return $this->choice('Extension type', $types);
    }

    /**
     * Ask the user to enter an extension name.
     *
     * @return string|null
     */
    private function askName(): ?string
    {
        $name = $this->ask('Extension name (e.g. Banner)');

        return $name !== null && trim($name) !== '' ? trim($name) : null;
    }

    /**
     * Build the method call chain for the extension configuration based on the selected options.
     *
     * @param bool $hasConfig
     * @param bool $hasTranslation
     * @param bool $hasView
     * @param bool $hasRoute
     * @param bool $hasAsset
     * @param bool $hasComponent
     * @param bool $hasConsoleKernel
     *
     * @return string
     */
    private function buildConfigurationChain(
        bool $hasConfig,
        bool $hasTranslation,
        bool $hasView,
        bool $hasRoute,
        bool $hasAsset,
        bool $hasComponent,
        bool $hasConsoleKernel
    ): string {
        $calls = [];
        if ($hasConfig) {
            $calls[] = '->hasConfig()';
        }
        if ($hasTranslation) {
            $calls[] = '->hasTranslation()';
        }
        if ($hasView) {
            $calls[] = '->hasView()';
        }
        if ($hasRoute) {
            $calls[] = '->hasRoute()';
        }
        if ($hasAsset) {
            $calls[] = '->hasAsset()';
        }
        if ($hasComponent) {
            $calls[] = '->hasComponent()';
        }
        if ($hasConsoleKernel) {
            $calls[] = '->hasConsoleKernel()';
        }
        if ($calls === []) {
            return '';
        }

        return '->' . ltrim(implode('', $calls), '->');
    }

    /**
     * Get the content of a stub file and replace the placeholders with the given values.
     *
     * @param string $name
     * @param array<string, string> $replace
     *
     * @return string
     * @throws FileNotFoundException
     */
    private function getStub(string $name, array $replace): string
    {
        $path = __DIR__ . '/stub/' . $name;
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
     * Write the content of a stub file to the given destination path after replacing the placeholders.
     * The destination file will be created if it does not exist, or overwritten if it already exists.
     *
     * @param string $destination
     * @param string $stubName
     * @param array<string, string> $replace
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeStub(string $destination, string $stubName, array $replace): void
    {
        $content = $this->getStub($stubName, $replace);
        if ($content !== '') {
            File::put($destination, $content);
        }
    }

    /**
     * Write the main extension class file using the extension.php.stub template and the given replacement values.
     * The generated class will be named {{name}} and will be placed in the {{extension}}/{{name}} directory.
     *
     * The stub will be processed to replace placeholders like {{extension}}, {{name}}, {{configurationChain}}, etc.
     * with the corresponding values from the $replace array. The generated class will extend the appropriate base
     * class based on the extension type and will include the configuration method with the specified configuration
     * chain.
     *
     * @param string $path
     * @param string $name
     * @param array<string, string> $replace
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeExtensionClass(string $path, string $name, array $replace): void
    {
        $content = $this->getStub('extension.php.stub', $replace);
        File::put($path . '/' . $name . '.php', $content);
    }

    /**
     * Write the extension.json file using the extension.json.stub template and the given replacement values.
     * The generated JSON file will contain the metadata of the extension, such as its name, version, author, etc.
     *
     * The stub will be processed to replace placeholders like {{extension}}, {{name}}, {{date}}, etc. with the
     * corresponding values from the $replace array. The generated JSON file will be used by the extension installer to
     * register the extension in the system and display its information in the UI.
     *
     * @param string $path
     * @param array<string, string> $replace
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function writeExtensionJson(string $path, array $replace): void
    {
        $content = $this->getStub('extension.json.stub', $replace);
        File::put($path . '/extension.json', $content);
    }
}
