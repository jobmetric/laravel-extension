<?php

namespace JobMetric\Extension\Kernel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use JobMetric\Extension\Contracts\AbstractExtension;
use JobMetric\Extension\Events\Kernel\Activated;
use JobMetric\Extension\Events\Kernel\Activating;
use JobMetric\Extension\Events\Kernel\Booted;
use JobMetric\Extension\Events\Kernel\Booting;
use JobMetric\Extension\Events\Kernel\ExtensionsDiscovered;
use JobMetric\Extension\Events\Kernel\ExtensionsLoaded;
use JobMetric\Extension\Events\Kernel\Registered;
use JobMetric\Extension\Events\Kernel\Registering;
use JobMetric\Extension\Facades\ExtensionNamespaceRegistry;
use JobMetric\Extension\Facades\ExtensionRegistry;
use JobMetric\Extension\Facades\ExtensionTypeRegistry;
use JobMetric\Extension\Models\Extension as ExtensionModel;

/**
 * Extension lifecycle kernel: discover, load installed, register, boot, activate.
 *
 * Runs after Laravel's provider register phase and before/during boot. Only extensions
 * that exist in the extensions table (installed) receive register(), boot(), activate().
 * Execution order is determined by AbstractExtension::priority() (lower runs first).
 *
 * Lifecycle phases:
 * 1. discover()     – Scan namespaces from ExtensionNamespaceRegistry; register FQCNs in ExtensionRegistry (no
 * instances).
 * 2. loadInstalledExtensions() – Load rows from extensions table; instantiate and add to kernel.
 * 3. registerExtensions() – Fire registering hooks, call register($app) on each extension, fire registered hooks.
 * 4. bootExtensions()    – Fire booting hooks, call boot($app) on each extension, fire booted hooks.
 * 5. activateExtensions() – Fire activating hooks, call activate($app) on each extension, fire activated hooks.
 *
 * Hooks (callbacks receive this kernel instance):
 * - registering / registered  – Before and after the register phase.
 * - booting / booted          – Before and after the boot phase.
 * - activating / activated    – Before and after the activate phase.
 *
 * @package JobMetric\Extension\Kernel
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
                    if (! is_file($classFile)) {
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
     */
    public function loadInstalledExtensions(): self
    {
        foreach ($this->loadingInstalledCallbacks as $callback) {
            $callback($this);
        }

        $extensions = ExtensionModel::all();

        foreach ($extensions as $extension) {
            $namespace = $extension->namespace ?? '';

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
     * @param AbstractExtension $extension Instance that will receive register(), boot(), activate().
     *
     * @return self
     */
    public function addExtension(AbstractExtension $extension): self
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Run the register phase: firing hooks and calling register($app) on each extension.
     *
     * Order: registering callbacks → extension->register($app) for each (by priority) → registered callbacks.
     * Extensions should only bind services or config in register(); no routes or runtime wiring.
     *
     * @return self
     */
    public function registerExtensions(): self
    {
        Event::dispatch(new Registering($this));

        foreach ($this->registeringCallbacks as $callback) {
            $callback($this);
        }
        foreach ($this->extensions() as $extension) {
            $extension->register($this->app);
        }
        foreach ($this->registeredCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new Registered($this));

        return $this;
    }

    /**
     * Run the boot phase: firing hooks and calling boot($app) on each extension.
     *
     * Order: booting callbacks → extension->boot($app) for each (by priority) → booted callbacks.
     * Extensions may register routes, events, view composers, etc. here.
     *
     * @return self
     */
    public function bootExtensions(): self
    {
        Event::dispatch(new Booting($this));

        foreach ($this->bootingCallbacks as $callback) {
            $callback($this);
        }
        foreach ($this->extensions() as $extension) {
            $extension->boot($this->app);
        }
        foreach ($this->bootedCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new Booted($this));

        return $this;
    }

    /**
     * Run the activate phase: firing hooks and calling activate($app) on each extension.
     *
     * Order: activating callbacks → extension->activate($app) for each (by priority) → activated callbacks.
     * Implementations should be idempotent (safe to run multiple times).
     *
     * @return self
     */
    public function activateExtensions(): self
    {
        Event::dispatch(new Activating($this));

        foreach ($this->activatingCallbacks as $callback) {
            $callback($this);
        }
        foreach ($this->extensions() as $extension) {
            $extension->activate($this->app);
        }
        foreach ($this->activatedCallbacks as $callback) {
            $callback($this);
        }

        Event::dispatch(new Activated($this));

        return $this;
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
        $this->clearCallbacks();

        return $this;
    }
}
