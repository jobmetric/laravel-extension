<?php

namespace JobMetric\Extension\Support;

use Illuminate\Support\Arr;

/**
 * Registry for extension types. Replaces the static list from ExtensionTypeEnum
 * with a dynamic list that can be extended at runtime via register() or via
 * config (extension.types). Used by extension:make and other commands to
 * validate and list allowed extension types.
 *
 * @package JobMetric\Extension
 *
 * @property-read array<string, array> $types Map of type name => options (internal state)
 */
class ExtensionTypeRegistry
{
    /**
     * Registered extension types: type name => options array.
     *
     * @var array<string, array>
     */
    protected array $types = [];

    /**
     * Register an extension type, or merge options for an existing type.
     *
     * @param string $type    Extension type name (e.g. Module, ShippingMethod).
     * @param array  $options Optional options (e.g. label, description, deletable).
     *
     * @return self
     */
    public function register(string $type, array $options = []): self
    {
        $this->types[$type] = array_merge(
            $this->types[$type] ?? [],
            $options
        );

        return $this;
    }

    /**
     * Remove an extension type from the registry.
     *
     * @param string $type Extension type name to remove.
     *
     * @return self
     */
    public function unregister(string $type): self
    {
        unset($this->types[$type]);

        return $this;
    }

    /**
     * Check whether an extension type is registered.
     *
     * @param string $type Extension type name to check.
     *
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->types[$type]);
    }

    /**
     * Get options for a registered extension type.
     *
     * @param string $type Extension type name.
     *
     * @return array|null Options array, or null if type is not registered.
     */
    public function get(string $type): ?array
    {
        return $this->types[$type] ?? null;
    }

    /**
     * Get all registered types and their options.
     *
     * @return array<string, array> Map of type name => options.
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * Get the list of registered extension type names (keys only).
     *
     * @return array<int, string>
     */
    public function values(): array
    {
        return array_keys($this->types);
    }

    /**
     * Get a single option for a registered type.
     *
     * @param string $type    Extension type name.
     * @param string $key    Option key (e.g. label, deletable).
     * @param mixed  $default Value when key is missing.
     *
     * @return mixed
     */
    public function getOption(string $type, string $key, mixed $default = null): mixed
    {
        return Arr::get($this->types[$type] ?? [], $key, $default);
    }

    /**
     * Remove all registered extension types.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->types = [];

        return $this;
    }
}
