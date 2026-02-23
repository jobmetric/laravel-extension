<?php

namespace JobMetric\Extension\Kernel;

/**
 * Holds lifecycle callback arrays and registration methods for ExtensionKernel.
 * Used by ExtensionKernel to keep the main class focused on discover/load/register/boot/activate flow.
 */
trait ExtensionKernelCallbacks
{
    /**
     * Callbacks run before register() is invoked on all extensions.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $registeringCallbacks = [];

    /**
     * Callbacks run after register() has been invoked on all extensions.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $registeredCallbacks = [];

    /**
     * Callbacks run before boot() is invoked on all extensions.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $bootingCallbacks = [];

    /**
     * Callbacks run after boot() has been invoked on all extensions.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $bootedCallbacks = [];

    /**
     * Callbacks run before activate() is invoked on all extensions.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $activatingCallbacks = [];

    /**
     * Callbacks run after activate() has been invoked on all extensions.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $activatedCallbacks = [];

    /**
     * Callbacks run before discover() scans the filesystem.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $discoveringCallbacks = [];

    /**
     * Callbacks run after discover() has registered FQCNs in ExtensionRegistry.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $discoveredCallbacks = [];

    /**
     * Callbacks run before loadInstalledExtensions() queries the database.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $loadingInstalledCallbacks = [];

    /**
     * Callbacks run after loadInstalledExtensions() has added instances to the kernel.
     *
     * @var array<int, callable(ExtensionKernel): void>
     */
    protected array $loadedInstalledCallbacks = [];

    /**
     * Register a callback to run before extension register() phase.
     *
     * @param callable(ExtensionKernel): void $callback Receives this kernel (e.g. to use app() or extensions()).
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function registering(callable $callback): self
    {
        $this->registeringCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run after extension register() phase.
     *
     * @param callable(ExtensionKernel): void $callback Receives this kernel.
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function registered(callable $callback): self
    {
        $this->registeredCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run before extension boot() phase.
     *
     * @param callable(ExtensionKernel): void $callback Receives this kernel.
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function booting(callable $callback): self
    {
        $this->bootingCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run after extension boot() phase.
     *
     * @param callable(ExtensionKernel): void $callback Receives this kernel.
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function booted(callable $callback): self
    {
        $this->bootedCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run before extension activate() phase.
     *
     * @param callable(ExtensionKernel): void $callback Receives this kernel.
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function activating(callable $callback): self
    {
        $this->activatingCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run after extension activate() phase.
     *
     * @param callable(ExtensionKernel): void $callback Receives this kernel.
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function activated(callable $callback): self
    {
        $this->activatedCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run before discover() scans the filesystem.
     *
     * @param callable(ExtensionKernel): void $callback
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function discovering(callable $callback): self
    {
        $this->discoveringCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run after discover() has registered FQCNs in ExtensionRegistry.
     *
     * @param callable(ExtensionKernel): void $callback
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function discovered(callable $callback): self
    {
        $this->discoveredCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run before loadInstalledExtensions() queries the database.
     *
     * @param callable(ExtensionKernel): void $callback
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function loadingInstalled(callable $callback): self
    {
        $this->loadingInstalledCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run after loadInstalledExtensions() has added instances to the kernel.
     *
     * @param callable(ExtensionKernel): void $callback
     *
     * @return ExtensionKernelCallbacks|ExtensionKernel
     */
    public function loadedInstalled(callable $callback): self
    {
        $this->loadedInstalledCallbacks[] = $callback;

        return $this;
    }

    /**
     * Clear all lifecycle callback arrays. Used by ExtensionKernel::reset().
     *
     * @return void
     */
    protected function clearCallbacks(): void
    {
        $this->registeringCallbacks = [];
        $this->registeredCallbacks = [];
        $this->bootingCallbacks = [];
        $this->bootedCallbacks = [];
        $this->activatingCallbacks = [];
        $this->activatedCallbacks = [];
        $this->discoveringCallbacks = [];
        $this->discoveredCallbacks = [];
        $this->loadingInstalledCallbacks = [];
        $this->loadedInstalledCallbacks = [];
    }
}
