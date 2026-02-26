<?php

namespace JobMetric\Extension\Kernel;

use DirectoryIterator;
use Illuminate\Support\Str;
use JobMetric\Extension\Exceptions\ExtensionCoreAssetFolderNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreClassNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreCommandClassNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreComponentFolderNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreConfigFileNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreConsoleKernelFileNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreDependencyPublishableClassNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreLangFolderNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreNameRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCorePublishablePathNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreRegisterClassTypeNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreRouteFileNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreViewFolderNotFoundException;
use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;

/**
 * Fluent config for one extension. Set in configuration(ExtensionCore);
 * applied in register/boot by ExtensionCoreBooter.
 * Paths are relative to extension base path.
 *
 * @package JobMetric\Extension
 *
 * @property string $name
 * @property array<string, mixed> $option
 */
class ExtensionCore
{
    /**
     * Extension display name (e.g. Module_Banner).
     * Set via name(); used for config keys and publish groups.
     *
     * @var string
     */
    public string $name;

    /**
     * Extension options. Keys depend on fluent methods called. Used in register/boot by ExtensionCoreBooter.
     * Options built by fluent methods: basePath, hasConfig, hasRoute, hasView, classes, commands, publishable, etc.
     *
     * @var array<string, mixed>
     */
    public array $option = [];

    /**
     * Set extension name. Required before register/boot and any method that uses name.
     *
     * @param string $name Non-empty (e.g. Module_Banner).
     *
     * @return static
     * @throws ExtensionCoreNameRequiredException
     */
    public function name(string $name): static
    {
        $name = trim($name);
        if ($name === '') {
            throw new ExtensionCoreNameRequiredException();
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Set extension root path. Must be an existing directory.
     *
     * @param string $path Absolute path to extension root.
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreBasePathNotFoundException
     */
    public function setBasePath(string $path): static
    {
        $path = trim($path);
        if ($path === '') {
            throw new ExtensionCoreBasePathRequiredException($this->name ?? '');
        }

        if (! is_dir($path)) {
            throw new ExtensionCoreBasePathNotFoundException($path, $this->name ?? '');
        }

        $this->option['basePath'] = $path;

        return $this;
    }

    /**
     * Set extension type (folder name, e.g. Module) and extension name (e.g. Banner). Used to build config key.
     *
     * @param string $type Folder/type (e.g. Module).
     * @param string $name Extension name (e.g. Banner).
     *
     * @return static
     */
    public function setExtensionTypeAndName(string $type, string $name): static
    {
        $this->option['extensionType'] = trim($type);
        $this->option['extensionName'] = trim($name);

        return $this;
    }

    /**
     * Enable config loading. Requires basePath/config.php (one config file per extension).
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreConfigFileNotFoundException
     */
    public function hasConfig(): static
    {
        if (! is_file($this->getBasePath() . DIRECTORY_SEPARATOR . 'config.php')) {
            $this->ensureNameSet();

            throw new ExtensionCoreConfigFileNotFoundException($this->name);
        }

        $this->option['hasConfig'] = true;

        return $this;
    }

    /**
     * Enable route file. Requires basePath/routes/route.php.
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreRouteFileNotFoundException
     */
    public function hasRoute(): static
    {
        if (! is_file($this->getBasePath() . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'route.php')) {
            $this->ensureNameSet();

            throw new ExtensionCoreRouteFileNotFoundException($this->name);
        }

        $this->option['hasRoute'] = true;

        return $this;
    }

    /**
     * Enable views from basePath/resources/views. Publishable = register for vendor:publish.
     *
     * @param bool $publishable
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreViewFolderNotFoundException
     */
    public function hasView(bool $publishable = false): static
    {
        if (! realpath($this->getBasePath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views')) {
            $this->ensureNameSet();

            throw new ExtensionCoreViewFolderNotFoundException($this->name);
        }

        $this->option['hasView'] = true;
        $this->option['isPublishableView'] = $publishable;

        return $this;
    }

    /**
     * Enable translations from basePath/lang.
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreLangFolderNotFoundException
     */
    public function hasTranslation(): static
    {
        if (! realpath($this->getBasePath() . DIRECTORY_SEPARATOR . 'lang')) {
            $this->ensureNameSet();

            throw new ExtensionCoreLangFolderNotFoundException($this->name);
        }

        $this->option['hasTranslation'] = true;

        return $this;
    }

    /**
     * Enable assets from basePath/assets. Publishable = register for vendor:publish.
     *
     * @param bool $publishable
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreAssetFolderNotFoundException
     */
    public function hasAsset(bool $publishable = false): static
    {
        if (! realpath($this->getBasePath() . DIRECTORY_SEPARATOR . 'assets')) {
            $this->ensureNameSet();

            throw new ExtensionCoreAssetFolderNotFoundException($this->name);
        }

        $this->option['hasAsset'] = true;
        $this->option['isPublishableAsset'] = $publishable;

        return $this;
    }

    /**
     * Enable Blade components from basePath/View/Components.
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreComponentFolderNotFoundException
     */
    public function hasComponent(): static
    {
        if (! realpath($this->getBasePath() . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Components')) {
            $this->ensureNameSet();

            throw new ExtensionCoreComponentFolderNotFoundException($this->name);
        }

        $this->option['hasComponent'] = true;

        return $this;
    }

    /**
     * Register class or factory in container. When string, class must exist.
     *
     * @param string $key                     Container key.
     * @param string|callable $classOrFactory FQCN or factory callable.
     * @param string $type                    bind|singleton|scoped|register.
     *
     * @return static
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreRegisterClassTypeNotFoundException
     * @throws ExtensionCoreClassNotFoundException
     */
    public function registerClass(string $key, string|callable $classOrFactory, string $type = 'bind'): static
    {
        if (! in_array($type, RegisterClassTypeEnum::values(), true)) {
            throw new ExtensionCoreRegisterClassTypeNotFoundException($type);
        }

        if (is_string($classOrFactory)) {
            $this->ensureNameSet();
            if (! class_exists($classOrFactory)) {
                throw new ExtensionCoreClassNotFoundException($this->name, $classOrFactory);
            }
        }

        if (! isset($this->option['classes'])) {
            $this->option['classes'] = [];
        }

        if (! array_key_exists($key, $this->option['classes'])) {
            $this->option['classes'][$key] = [
                'class' => $classOrFactory,
                'type'  => $type,
            ];
        }

        return $this;
    }

    /**
     * Register Artisan command to load when extension boots. Class must exist.
     *
     * @param string $class Command FQCN.
     *
     * @return static
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreCommandClassNotFoundException
     */
    public function registerCommand(string $class): static
    {
        $this->ensureNameSet();

        if (! class_exists($class)) {
            throw new ExtensionCoreCommandClassNotFoundException($this->name, $class);
        }

        if (! isset($this->option['commands'])) {
            $this->option['commands'] = [];
        }

        if (! in_array($class, $this->option['commands'], true)) {
            $this->option['commands'][] = $class;
        }

        return $this;
    }

    /**
     * Register publishable paths (source => destination). Source paths validated; relative = to basePath.
     *
     * @param array<string, string> $paths
     * @param string|array<int, string>|null $groups Defaults to extension name.
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCorePublishablePathNotFoundException
     */
    public function registerPublishable(array $paths, string|array|null $groups = null): static
    {
        $this->ensureNameSet();
        $basePath = $this->getBasePath();
        foreach ($paths as $source => $destination) {
            $fullSource = $this->resolvePublishableSourcePath($source, $basePath);
            if (! realpath($fullSource)) {
                throw new ExtensionCorePublishablePathNotFoundException($this->name, $source);
            }
        }

        if ($groups === null) {
            $groups = $this->name;
        }

        if (is_string($groups)) {
            $groups = [$groups];
        }

        if (! in_array($this->name, $groups, true)) {
            $groups[] = $this->name;
        }

        if (! isset($this->option['publishable'])) {
            $this->option['publishable'] = [];
        }

        $this->option['publishable'][md5(implode(',', $groups))] = [
            'paths'  => $paths,
            'groups' => $groups,
        ];

        return $this;
    }

    /**
     * Resolve source path: absolute unchanged, relative = basePath + path.
     *
     * @param string $path
     * @param string $basePath
     *
     * @return string
     */
    private function resolvePublishableSourcePath(string $path, string $basePath): string
    {
        $path = trim($path);
        if ($path === '') {
            return $basePath;
        }

        if (str_starts_with($path, DIRECTORY_SEPARATOR) || (strlen($path) > 1 && $path[1] === ':')) {
            return $path;
        }

        return rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Register dependency provider whose publishables are merged. Provider class must exist.
     *
     * @param string $provider Provider FQCN.
     * @param string|null $group
     *
     * @return static
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreDependencyPublishableClassNotFoundException
     */
    public function registerDependencyPublishable(string $provider, ?string $group = null): static
    {
        $this->ensureNameSet();

        if (! class_exists($provider)) {
            throw new ExtensionCoreDependencyPublishableClassNotFoundException($this->name, $provider);
        }

        if (! isset($this->option['dependency_publishable'])) {
            $this->option['dependency_publishable'] = [];
        }

        $this->option['dependency_publishable'][] = [
            'provider' => $provider,
            'group'    => $group,
        ];

        return $this;
    }

    /**
     * Enable ConsoleKernel for scheduling. Requires basePath/ConsoleKernel.php; schedule() called from app Kernel.
     *
     * @return static
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreConsoleKernelFileNotFoundException
     */
    public function hasConsoleKernel(): static
    {
        if (! is_file($this->getBasePath() . DIRECTORY_SEPARATOR . 'ConsoleKernel.php')) {
            $this->ensureNameSet();

            throw new ExtensionCoreConsoleKernelFileNotFoundException($this->name);
        }

        $this->option['hasConsoleKernel'] = true;

        return $this;
    }

    /**
     * Short name for config/view keys (backslashes and dashes → underscore).
     *
     * @return string
     * @throws ExtensionCoreNameRequiredException
     */
    public function shortName(): string
    {
        $this->ensureNameSet();

        return Str::of($this->name)->replace(['\\', '-'], '_')->toString();
    }

    /**
     * Config/key prefix: extension_{type}_{name} (e.g. extension_module_banner). Uses stored type/name when set.
     *
     * @return string
     * @throws ExtensionCoreNameRequiredException
     */
    public function getConfigKey(): string
    {
        if (isset($this->option['extensionType'], $this->option['extensionName']) && ($this->option['extensionType'] !== '') && ($this->option['extensionName'] !== '')) {
            return 'extension_' . Str::snake($this->option['extensionType']) . '_' . Str::snake($this->option['extensionName']);
        }

        return 'extension_' . Str::snake($this->shortName());
    }

    /**
     * Extension root path (set via setBasePath).
     *
     * @return string
     * @throws ExtensionCoreBasePathRequiredException
     */
    public function getBasePath(): string
    {
        if (($path = $this->option['basePath'] ?? '') === '') {
            throw new ExtensionCoreBasePathRequiredException($this->name ?? '');
        }

        return $path;
    }

    /**
     * Detect namespace from first PHP file in base path.
     *
     * @return string|null
     * @throws ExtensionCoreBasePathRequiredException
     */
    public function getNamespace(): ?string
    {
        foreach (new DirectoryIterator($this->getBasePath()) as $fileInfo) {
            if (! $fileInfo->isDot() && $fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                if (preg_match('/namespace\s+([^;]+);/i', file_get_contents($fileInfo->getPathname()), $m)) {
                    return trim($m[1]);
                }
            }
        }

        return null;
    }

    /**
     * Throw if name not set. Used before methods that need $this->name.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    protected function ensureNameSet(): void
    {
        if (! isset($this->name) || trim($this->name) === '') {
            throw new ExtensionCoreNameRequiredException();
        }
    }
}
