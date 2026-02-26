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
        {--m|multiple : Allow multiple plugin instances}
        {--c|config : Has config (config.php)}
        {--t|translation : Has translation (lang)}
        {--v|view : Has views (resources/views)}
        {--r|route : Has routes (routes/route.php)}
        {--a|asset : Has assets}
        {--p|component : Has Blade component (View/Components)}
        {--k|console-kernel : Has ConsoleKernel (schedule)}';

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

        $path = base_path(appFolderName() . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $name);
        if (File::isDirectory($path)) {
            $this->error('Extension already exists: ' . $extension . '/' . $name);

            return 2;
        }

        if (! ExtensionTypeRegistry::has($extension)) {
            $this->error('Extension type not registered: ' . $extension . '. Register it in config extension.types.');

            return 3;
        }

        $multiple = $this->resolveBooleanOption('multiple', fn () => $this->confirm('Multiple plugin instances?'), false);
        $hasConfig = $this->resolveBooleanOption('config', fn () => $this->confirm('Has config (config.php)?', true), true);
        $hasTranslation = $this->resolveBooleanOption('translation', fn () => $this->confirm('Has translation (lang)?', true), true);
        $hasView = $this->resolveBooleanOption('view', fn () => $this->confirm('Has views (resources/views)?'), false);
        $hasRoute = $this->resolveBooleanOption('route', fn () => $this->confirm('Has routes (routes/route.php)?'), false);
        $hasAsset = $this->resolveBooleanOption('asset', fn () => $this->confirm('Has assets (assets)?'), false);
        $hasComponent = $this->resolveBooleanOption('component', fn () => $this->confirm('Has Blade component (View/Components)?'), false);
        $hasConsoleKernel = $this->resolveBooleanOption('console-kernel', fn () => $this->confirm('Has ConsoleKernel (schedule)?'), false);

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
        [$replace['configurationThrowsUse'], $replace['configurationThrows']] = $this->buildConfigurationThrows($hasConfig, $hasTranslation, $hasView, $hasRoute, $hasAsset, $hasComponent, $hasConsoleKernel);

        File::ensureDirectoryExists($path);

        $this->writeExtensionClass($path, $name, $replace);
        $this->writeExtensionJson($path, $replace);

        if ($hasConfig) {
            $this->writeStub($path . DIRECTORY_SEPARATOR . 'config.php', 'config.php.stub', $replace);
        }

        if ($hasTranslation) {
            $langPath = $path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'en';
            File::ensureDirectoryExists($langPath);
            $this->writeStub($langPath . DIRECTORY_SEPARATOR . 'extension.php', 'lang-extension.php.stub', $replace);
        }

        if ($hasView) {
            $viewPath = $path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
            File::ensureDirectoryExists($viewPath);
            $this->writeStub($viewPath . DIRECTORY_SEPARATOR . 'sample.blade.php', 'view.blade.php.stub', $replace);
        }

        if ($hasRoute) {
            $routeDir = $path . DIRECTORY_SEPARATOR . 'routes';
            File::ensureDirectoryExists($routeDir);
            $this->writeStub($routeDir . DIRECTORY_SEPARATOR . 'route.php', 'route.php.stub', $replace);
        }

        if ($hasAsset) {
            File::ensureDirectoryExists($path . DIRECTORY_SEPARATOR . 'assets');
            File::put($path . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . '.gitkeep', '');
        }

        if ($hasComponent) {
            $componentDir = $path . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Components';
            File::ensureDirectoryExists($componentDir);
            $this->writeStub($componentDir . DIRECTORY_SEPARATOR . $name . 'Component.php', 'Component.php.stub', $replace);
            $viewDir = $path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'components';
            File::ensureDirectoryExists($viewDir);
            $this->writeStub($viewDir . DIRECTORY_SEPARATOR . Str::snake($name) . '-component.blade.php', 'component-view.blade.php.stub', $replace);
        }

        if ($hasConsoleKernel) {
            $this->writeStub($path . DIRECTORY_SEPARATOR . 'ConsoleKernel.php', 'ConsoleKernel.php.stub', $replace);
        }

        $this->info('Extension [' . $extension . '/' . $name . '] created successfully.');

        return 0;
    }

    /**
     * Resolve a boolean from option: if option passed return true, otherwise ask when interactive or return default.
     *
     * @param string $option
     * @param callable $ask
     * @param bool $default
     *
     * @return bool
     */
    private function resolveBooleanOption(string $option, callable $ask, bool $default): bool
    {
        if ($this->option($option)) {
            return true;
        }
        if ($this->input->isInteractive()) {
            return (bool) $ask();
        }
        return $default;
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
     * Build the list of exceptions that the configuration method may throw based on the selected options.
     *
     * @param bool $hasConfig
     * @param bool $hasTranslation
     * @param bool $hasView
     * @param bool $hasRoute
     * @param bool $hasAsset
     * @param bool $hasComponent
     * @param bool $hasConsoleKernel
     *
     * @return array{0: string, 1: string}
     */
    private function buildConfigurationThrows(
        bool $hasConfig,
        bool $hasTranslation,
        bool $hasView,
        bool $hasRoute,
        bool $hasAsset,
        bool $hasComponent,
        bool $hasConsoleKernel
    ): array {
        $exceptions = [];
        if ($hasConfig) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreConfigFileNotFoundException';
        }
        if ($hasTranslation) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreLangFolderNotFoundException';
        }
        if ($hasView) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreViewFolderNotFoundException';
        }
        if ($hasRoute) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreRouteFileNotFoundException';
        }
        if ($hasAsset) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreAssetFolderNotFoundException';
        }
        if ($hasComponent) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreComponentFolderNotFoundException';
        }
        if ($hasConsoleKernel) {
            $exceptions[] = 'ExtensionCoreBasePathRequiredException';
            $exceptions[] = 'ExtensionCoreNameRequiredException';
            $exceptions[] = 'ExtensionCoreConsoleKernelFileNotFoundException';
        }
        $unique = array_unique($exceptions);
        if ($unique === []) {
            return ['', ''];
        }
        $namespace = 'JobMetric\\Extension\\Exceptions';
        $useLines = array_map(fn (string $class) => 'use ' . $namespace . '\\' . $class . ';', $unique);
        $useBlock = "\n" . implode("\n", $useLines);
        $throwsBlock = implode("\n * ", array_map(fn (string $class) => '@throws ' . $class, $unique));

        return [$useBlock, $throwsBlock];
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
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . $name;
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
        File::put($path . DIRECTORY_SEPARATOR . $name . '.php', $content);
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
        File::put($path . DIRECTORY_SEPARATOR . 'extension.json', $content);
    }
}
