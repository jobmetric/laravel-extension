<?php

namespace JobMetric\Extension\Kernel;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Events\Kernel\Booted;
use JobMetric\Extension\Events\Kernel\Booting;
use JobMetric\Extension\Events\Kernel\ExtensionsDiscovered;
use JobMetric\Extension\Events\Kernel\ExtensionsLoaded;
use JobMetric\Extension\Events\Kernel\Registered;
use JobMetric\Extension\Events\Kernel\Registering;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreNameRequiredException;
use JobMetric\Extension\Facades\ExtensionNamespaceRegistry;
use JobMetric\Extension\Facades\ExtensionRegistry;
use JobMetric\Extension\Facades\ExtensionTypeRegistry;
use JobMetric\Extension\Facades\InstalledExtensionsFile;
use ReflectionClass;

/**
 * Extension lifecycle kernel: discover, load installed, register, boot.
 *
 * Runs after Laravel's provider register phase and before/during boot. Only extensions
 * that exist in the extensions table (installed) receive register() and boot().
 * Execution order is determined by AbstractExtension::priority() (lower runs first).
 *
 * Lifecycle phases:
 * 1. discover()                – Scan namespaces from ExtensionNamespaceRegistry; register FQCNs in ExtensionRegistry
 *                                (no instances).
 * 2. loadInstalledExtensions() – Load rows from extensions table; instantiate and add to kernel.
 * 3. registerExtensions()      – Fire registering hooks, call configuration(ExtensionCore) on each extension and apply
 *                                register (config, bindings), fire registered hooks.
 * 4. bootExtensions()          – Fire booting hooks, apply boot (migrations, routes, views, translations) per
 *                                extension, fire booted hooks.
 *
 * Hooks (callbacks receive this kernel instance):
 * - registering / registered   – Before and after the register phase.
 * - booting / booted           – Before and after the boot phase.
 *
 * @package JobMetric\Extension
 */
class ExtensionKernel
{
    use ExtensionKernelCallbacks;

    /**
     * Extension instances loaded from the extensions table (installed only).
     *
     * @var array<int, AbstractExtension>
     */
    protected array $extensions = [];

    /**
     * ExtensionCore per extension class (FQCN => ExtensionCore), built during registerExtensions().
     *
     * @var array<string, ExtensionCore>
     */
    protected array $extensionCores = [];

    /**
     * @param Application $app
     */
    public function __construct(
        protected Application $app
    ) {
    }

    /**
     * Get the application instance.
     *
     * @return Application
     */
    public function app(): Application
    {
        return $this->app;
    }

    /**
     * Discover extension classes from the filesystem and register them in ExtensionRegistry.
     *
     * Fires discovering callbacks, then either restores from cache (if discover_cache_ttl > 0)
     * or scans namespaces, builds list, caches it, and registers each FQCN. Finally fires
     * discovered callbacks and ExtensionsDiscovered event.
     *
     * @return self
     */
    public function discover(): self
    {
        foreach ($this->discoveringCallbacks as $callback) {
            $callback($this);
        }

        $ttl = (int) config('extension.discover_cache_ttl', 0);
        $cacheKey = config('extension.discover_cache_key', 'extension_kernel.discovered');

        if ($ttl > 0) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                foreach ($cached as $item) {
                    if (isset($item['type'], $item['namespace'])) {
                        ExtensionRegistry::register($item['type'], $item['namespace']);
                    }
                }

                foreach ($this->discoveredCallbacks as $callback) {
                    $callback($this);
                }

                Event::dispatch(new ExtensionsDiscovered($this));

                return $this;
            }
        }

        $discovered = [];
        foreach (ExtensionNamespaceRegistry::all() as $baseNamespace) {
            $basePath = resolveNamespacePath($baseNamespace);
            if ($basePath === null || ! is_dir($basePath)) {
                continue;
            }

            $typeDirs = array_filter(glob($basePath . '/*', GLOB_ONLYDIR) ?: [], 'is_dir');
            foreach ($typeDirs as $typeDir) {
                $type = Str::studly(basename($typeDir));
                if (! ExtensionTypeRegistry::has($type)) {
                    continue;
                }

                $extensionDirs = array_filter(glob($typeDir . '/*', GLOB_ONLYDIR) ?: [], 'is_dir');
                foreach ($extensionDirs as $extDir) {
                    $name = basename($extDir);
                    $classFile = $extDir . DIRECTORY_SEPARATOR . $name . '.php';
                    $extensionJson = $extDir . DIRECTORY_SEPARATOR . 'extension.json';
                    if (! is_file($classFile) || ! is_file($extensionJson)) {
                        continue;
                    }

                    $fqcn = $baseNamespace . '\\' . $type . '\\' . $name . '\\' . $name;
                    if (! class_exists($fqcn)) {
                        continue;
                    }

                    if (! is_subclass_of($fqcn, AbstractExtension::class)) {
                        continue;
                    }

                    ExtensionRegistry::register($type, $fqcn);

                    $discovered[] = ['type' => $type, 'namespace' => $fqcn];
                }
            }
        }

        if ($ttl > 0 && $cacheKey !== '') {
            Cache::put($cacheKey, $discovered, $ttl);
        }

        foreach ($this->discoveredCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new ExtensionsDiscovered($this));

        return $this;
    }

    /**
     * Clear the discover cache so the next discover() will rescan the filesystem.
     *
     * @return void
     */
    public static function clearDiscoverCache(): void
    {
        Cache::forget(config('extension.discover_cache_key', 'extension_kernel.discovered'));
    }

    /**
     * Load installed extensions from the extensions table and add their instances to the kernel.
     *
     * Fires loadingInstalled callbacks, queries the extensions table, resolves each namespace
     * via the container and adds instances. Then fires loadedInstalled callbacks and
     * ExtensionsLoaded event.
     *
     * @return self
     * @throws BindingResolutionException When the container cannot resolve a namespace.
     * @throws FileNotFoundException
     */
    public function loadInstalledExtensions(): self
    {
        foreach ($this->loadingInstalledCallbacks as $callback) {
            $callback($this);
        }

        $namespaces = InstalledExtensionsFile::read();
        foreach ($namespaces as $namespace) {
            if ($namespace === '') {
                continue;
            }

            if (! class_exists($namespace)) {
                continue;
            }

            if (! is_subclass_of($namespace, AbstractExtension::class)) {
                continue;
            }

            $this->addExtension($this->app->make($namespace));
        }

        foreach ($this->loadedInstalledCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new ExtensionsLoaded($this));

        return $this;
    }

    /**
     * Add a single extension instance to the kernel list (e.g. for testing or manual registration).
     *
     * @param AbstractExtension $extension Instance that will receive configuration(ExtensionCore) during register
     *                                     phase.
     *
     * @return self
     */
    public function addExtension(AbstractExtension $extension): self
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Run the register phase: firing hooks, calling configuration(ExtensionCore) on each extension and applying
     * config/bindings, then registered callbacks.
     *
     * @return self
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function registerExtensions(): self
    {
        Event::dispatch(new Registering($this));

        foreach ($this->registeringCallbacks as $callback) {
            $callback($this);
        }

        foreach ($this->extensions() as $extension) {
            $core = $this->buildExtensionCore($extension);
            $extension->configuration($core);
            $this->extensionCores[get_class($extension)] = $core;

            ExtensionCoreBooter::register($core, $this->app, $extension);
        }

        foreach ($this->registeredCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new Registered($this));

        return $this;
    }

    /**
     * Run the boot phase: firing hooks and applying routes, views, translations, commands, publishable, etc. per
     * extension, then booted callbacks. Pass a ServiceProvider for commands; pass publishCallback so the provider
     * can call its protected publishes() from within its own context.
     *
     * @param ServiceProvider|null $provider
     * @param callable(array, string|array): void|null $publishCallback e.g. fn($paths, $groups) =>
     *                                                                  $this->publishes($paths, $groups)
     *
     * @return self
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function bootExtensions(?ServiceProvider $provider = null, ?callable $publishCallback = null): self
    {
        Event::dispatch(new Booting($this));

        foreach ($this->bootingCallbacks as $callback) {
            $callback($this);
        }

        foreach ($this->extensions() as $extension) {
            $fqcn = get_class($extension);
            if (isset($this->extensionCores[$fqcn])) {
                ExtensionCoreBooter::boot($this->extensionCores[$fqcn], $this->app, $provider, $extension, $publishCallback);
            }
        }

        foreach ($this->bootedCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new Booted($this));

        return $this;
    }

    /**
     * Build ExtensionCore for an extension with basePath and name set.
     *
     * @param AbstractExtension $extension
     *
     * @return ExtensionCore
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreBasePathNotFoundException
     */
    protected function buildExtensionCore(AbstractExtension $extension): ExtensionCore
    {
        $type = Str::studly($extension::extension());
        $extensionName = Str::studly($extension::name());

        $core = new ExtensionCore();
        $core->name($type . '_' . $extensionName)
            ->setBasePath(dirname((new ReflectionClass($extension))->getFileName()))
            ->setExtensionTypeAndName($type, $extensionName);

        return $core;
    }

    /**
     * Get all loaded extension instances sorted by dependencies then priority.
     *
     * @return array<int, AbstractExtension>
     */
    public function extensions(): array
    {
        return $this->sortByDependenciesAndPriority($this->extensions);
    }

    /**
     * Register schedules from all extensions that have hasConsoleKernel. Call this from App\Console\Kernel::schedule().
     *
     * @param Schedule $schedule
     *
     * @return void
     * @throws BindingResolutionException
     * @throws ExtensionCoreBasePathRequiredException
     */
    public function registerSchedules(Schedule $schedule): void
    {
        foreach ($this->extensionCores as $core) {
            if (! isset($core->option['hasConsoleKernel'])) {
                continue;
            }

            $namespace = $core->getNamespace();
            if ($namespace === null) {
                continue;
            }

            $consoleKernelClass = $namespace . '\\ConsoleKernel';
            if (! class_exists($consoleKernelClass)) {
                continue;
            }

            $kernel = $this->app->make($consoleKernelClass);
            if (method_exists($kernel, 'schedule')) {
                $kernel->schedule($schedule);
            }
        }
    }

    /**
     * Sort extensions: first by dependency order (dependencies before dependants), then by priority.
     *
     * @param array<int, AbstractExtension> $list
     *
     * @return array<int, AbstractExtension>
     */
    protected function sortByDependenciesAndPriority(array $list): array
    {
        if (count($list) === 0) {
            return [];
        }

        $byFqcn = [];
        foreach ($list as $ext) {
            $byFqcn[get_class($ext)] = $ext;
        }
        $fqcnList = array_keys($byFqcn);

        $inDegree = array_fill_keys($fqcnList, 0);
        $outEdges = array_fill_keys($fqcnList, []);

        foreach ($list as $ext) {
            $fqcn = get_class($ext);
            foreach ($ext::depends() as $dep) {
                $dep = ltrim($dep, '\\');
                if (isset($byFqcn[$dep])) {
                    $outEdges[$dep][] = $fqcn;
                    $inDegree[$fqcn]++;
                }
            }
        }

        $queue = [];
        foreach ($fqcnList as $fqcn) {
            if ($inDegree[$fqcn] === 0) {
                $queue[] = $fqcn;
            }
        }
        $sortQueue = function (array $q) use ($byFqcn): array {
            usort($q, function (string $a, string $b) use ($byFqcn): int {
                return $byFqcn[$a]::priority() <=> $byFqcn[$b]::priority();
            });

            return $q;
        };
        $queue = $sortQueue($queue);

        $result = [];
        while ($queue !== []) {
            $fqcn = array_shift($queue);
            $result[] = $byFqcn[$fqcn];
            foreach ($outEdges[$fqcn] as $m) {
                $inDegree[$m]--;
                if ($inDegree[$m] === 0) {
                    $queue[] = $m;
                }
            }
            $queue = $sortQueue($queue);
        }

        foreach ($fqcnList as $fqcn) {
            if ($inDegree[$fqcn] > 0) {
                $result[] = $byFqcn[$fqcn];
            }
        }

        return $result;
    }

    /**
     * Find an extension by type and name (e.g. Module, Banner).
     *
     * @param string $type Extension type (e.g. Module).
     * @param string $name Extension name (e.g. Banner).
     *
     * @return AbstractExtension|null
     */
    public function getExtension(string $type, string $name): ?AbstractExtension
    {
        $type = Str::studly($type);
        $name = Str::studly($name);

        foreach ($this->extensions() as $extension) {
            if (Str::studly($extension::extension()) === $type && Str::studly($extension::name()) === $name) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Find an extension by its FQCN.
     *
     * @param string $fqcn Fully qualified class name.
     *
     * @return AbstractExtension|null
     */
    public function getExtensionByClass(string $fqcn): ?AbstractExtension
    {
        $fqcn = ltrim($fqcn, '\\');

        foreach ($this->extensions() as $extension) {
            if (get_class($extension) === $fqcn) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Clear the loaded extension list (e.g. for tests). Hooks and cache are unchanged.
     *
     * @return self
     */
    public function clearExtensions(): self
    {
        $this->extensions = [];

        return $this;
    }

    /**
     * Reset the kernel: clear extensions and all hook callbacks. Does not clear discover cache.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->extensions = [];
        $this->extensionCores = [];
        $this->clearCallbacks();

        return $this;
    }
}
