<?php

namespace JobMetric\Extension\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use JobMetric\Extension\Contracts\AbstractExtension;
use Throwable;

/**
 * Registry for extension class namespaces grouped by type.
 *
 * Stores only type => [namespaces] (no spec). When spec is needed, use
 * resolveSpec() to instantiate the class and get it from AbstractExtension::toArray().
 *
 * @package JobMetric\Extension
 *
 * @property-read array<string, array<int, string>> $extensions Map of type => namespaces (internal state)
 */
class ExtensionRegistry
{
    /**
     * Registered extensions: type => list of namespaces.
     *
     * @var array<string, array<int, string>>
     */
    protected array $extensions = [];

    /**
     * Register an extension namespace under the given type.
     *
     * @param string $type      Extension type (e.g. Module, ShippingMethod).
     * @param string $namespace Extension class FQCN (e.g. App\Extensions\Module\Banner\Banner).
     *
     * @return self
     */
    public function register(string $type, string $namespace): self
    {
        $type = Str::studly($type);
        if (!isset($this->extensions[$type])) {
            $this->extensions[$type] = [];
        }
        if (!in_array($namespace, $this->extensions[$type], true)) {
            $this->extensions[$type][] = $namespace;
        }

        return $this;
    }

    /**
     * Remove an extension from the registry by namespace.
     *
     * @param string $namespace Extension class namespace to remove.
     *
     * @return self
     */
    public function unregister(string $namespace): self
    {
        foreach ($this->extensions as $type => $namespaces) {
            $key = array_search($namespace, $namespaces, true);
            if ($key !== false) {
                unset($this->extensions[$type][$key]);
                $this->extensions[$type] = array_values($this->extensions[$type]);
                if (empty($this->extensions[$type])) {
                    unset($this->extensions[$type]);
                }
                break;
            }
        }

        return $this;
    }

    /**
     * Check whether an extension namespace is registered.
     *
     * @param string $namespace Extension class namespace to check.
     *
     * @return bool True if registered, false otherwise.
     */
    public function has(string $namespace): bool
    {
        return $this->get($namespace) !== null;
    }

    /**
     * Get the type for a registered extension namespace.
     *
     * @param string $namespace Extension class namespace.
     *
     * @return string|null Type (e.g. Module), or null if not registered.
     */
    public function get(string $namespace): ?string
    {
        foreach ($this->extensions as $type => $namespaces) {
            if (in_array($namespace, $namespaces, true)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Get all registered extensions: type => list of namespaces.
     *
     * @return array<string, array<int, string>>
     */
    public function all(): array
    {
        return $this->extensions;
    }

    /**
     * Get the list of all registered extension namespaces.
     *
     * @return array<int, string>
     */
    public function values(): array
    {
        return array_merge(...array_values($this->extensions));
    }

    /**
     * Get all namespaces registered for a given type.
     *
     * @param string $type Extension type (e.g. Module).
     *
     * @return array<int, string> List of namespaces.
     */
    public function byType(string $type): array
    {
        $type = Str::studly($type);

        return $this->extensions[$type] ?? [];
    }

    /**
     * Find a namespace by type and extension name (e.g. Module, Banner).
     * Name is derived from the class name (last segment of namespace).
     *
     * @param string $type Extension type (e.g. Module).
     * @param string $name Extension name (e.g. Banner).
     *
     * @return string|null Namespace, or null if not found.
     */
    public function byTypeAndName(string $type, string $name): ?string
    {
        $type = Str::studly($type);
        $name = Str::studly($name);

        foreach ($this->byType($type) as $namespace) {
            $parts = explode('\\', $namespace);
            $className = end($parts);
            if ($className === $name) {
                return $namespace;
            }
        }

        return null;
    }

    /**
     * Resolve the full spec for an extension by instantiating the class and calling toArray().
     * Only works for classes that extend AbstractExtension.
     *
     * @param string $namespace Extension class namespace.
     *
     * @return array<string, mixed>|null Spec array (extension, name, version, title, form, etc.), or null if not loadable.
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function resolveSpec(string $namespace): ?array
    {
        if (!class_exists($namespace)) {
            return null;
        }

        $instance = app()->make($namespace);
        if (!$instance instanceof AbstractExtension) {
            return null;
        }

        return $instance->toArray();
    }

    /**
     * Remove all registered extensions from the registry.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->extensions = [];

        return $this;
    }
}
