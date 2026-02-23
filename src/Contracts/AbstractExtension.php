<?php

namespace JobMetric\Extension\Contracts;

use Illuminate\Contracts\Foundation\Application;
use JobMetric\Form\FormBuilder;
use Throwable;

/**
 * Base class for every extension in this package.
 *
 * Extensions are identified by type and name, have metadata (version, title,
 * description, author, etc.) and define their plugin configuration via form().
 * toArray() exports that metadata and the form definition for install/list.
 * handle() runs the extension logic with the given plugin data.
 *
 * Concrete extensions must implement: extension(), name(), version(), title(),
 * multiple(), form(), and handle(). The rest have defaults and can be overridden.
 *
 * @package JobMetric\Extension
 */
abstract class AbstractExtension
{
    /**
     * Type of this extension (e.g. Module, ShippingMethod).
     * Must be one of the types registered in this package's ExtensionTypeRegistry.
     *
     * @return string
     */
    abstract public static function extension(): string;

    /**
     * Short name/slug of this extension (e.g. Banner).
     * Used in directory layout and as the extension runner class name.
     *
     * @return string
     */
    abstract public static function name(): string;

    /**
     * Extension version (semantic, e.g. 1.0.0).
     *
     * @return string
     */
    abstract public static function version(): string;

    /**
     * Human-readable title (translation key or plain text).
     * Shown in the extension list and when creating the first plugin.
     *
     * @return string
     */
    abstract public static function title(): string;

    /**
     * True if this extension can have more than one plugin; false for a single plugin per extension.
     *
     * @return bool
     */
    abstract public static function multiple(): bool;

    /**
     * Execution priority. Lower value runs first (register, boot). Override to change.
     *
     * @return int
     */
    public static function priority(): int
    {
        return 0;
    }

    /**
     * FQCNs of extensions this one depends on; they will run before this extension.
     * Used for topological sort. Override to declare dependencies.
     *
     * @return array<int, string>
     */
    public static function depends(): array
    {
        return [];
    }

    /**
     * Short description of the extension (translation key or plain text).
     * Shown in the extension list. Override to change the default.
     *
     * @return string
     */
    public static function description(): string
    {
        return 'extension::base.extension.default_description';
    }

    /**
     * Author name. Override to set.
     *
     * @return string|null
     */
    public static function author(): ?string
    {
        return 'Job Metric';
    }

    /**
     * Author email. Override to set.
     *
     * @return string|null
     */
    public static function email(): ?string
    {
        return 'jobmetricnet@gmail.com';
    }

    /**
     * Author or extension website URL. Override to set.
     *
     * @return string|null
     */
    public static function website(): ?string
    {
        return 'https://jobmetric.github.io';
    }

    /**
     * Date when the extension was created (e.g. Y-m-d). Override to set.
     *
     * @return string|null
     */
    public static function creationDate(): ?string
    {
        return null;
    }

    /**
     * Copyright text. Override to set.
     *
     * @return string|null
     */
    public static function copyright(): ?string
    {
        return 'Job Metric Copyright (' . date('Y') . ')';
    }

    /**
     * License name (e.g. MIT). Override to set.
     *
     * @return string|null
     */
    public static function license(): ?string
    {
        return 'Job Metric Licence';
    }

    /**
     * Build and return the form used to configure a plugin for this extension.
     * The installer and plugin UI use this to render and validate plugin fields.
     *
     * @return FormBuilder
     */
    abstract public function form(): FormBuilder;

    /**
     * Export extension metadata and form definition as an array.
     * Used by the installer and listing; structure matches what extension.json used to provide.
     * The "form" key holds the full form definition (tabs, fields, etc.) from form()->build()->toArray().
     *
     * @return array{
     *     extension: string,
     *     name: string,
     *     version: string,
     *     title: string,
     *     description: string,
     *     multiple: bool,
     *     author?: string|null,
     *     email?: string|null,
     *     website?: string|null,
     *     creationDate?: string|null,
     *     copyright?: string|null,
     *     license?: string|null,
     *     form: array<string, mixed>
     * }
     *
     * @throws Throwable
     */
    public function toArray(): array
    {
        return [
            'extension'    => static::extension(),
            'name'         => static::name(),
            'version'      => static::version(),
            'title'        => static::title(),
            'description'  => static::description(),
            'multiple'     => static::multiple(),
            'author'       => static::author(),
            'email'        => static::email(),
            'website'      => static::website(),
            'creationDate' => static::creationDate(),
            'copyright'    => static::copyright(),
            'license'      => static::license(),
            'form'         => $this->form()->build()->toArray(),
        ];
    }

    /**
     * Execute this extension with the given plugin data (field values and options).
     * Concrete extensions implement the actual logic here.
     *
     * @param array<string, mixed> $options Plugin field values and any extra options.
     *
     * @return string|null Result or output; null if nothing to return.
     */
    abstract public function handle(array $options = []): ?string;

    /**
     * Register bindings and configuration. Only interact with the container.
     *
     * @param Application $context
     *
     * @return void
     */
    public function register(Application $context): void
    {
    }

    /**
     * Bootstrap runtime wiring (routes, events, etc.). Run after all providers have booted.
     *
     * @param Application $context
     *
     * @return void
     */
    public function boot(Application $context): void
    {
    }
}
