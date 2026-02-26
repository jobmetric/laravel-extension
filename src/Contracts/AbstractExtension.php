<?php

namespace JobMetric\Extension\Contracts;

use JobMetric\Extension\Kernel\EventTrait;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Form\FormBuilder;
use ReflectionClass;
use Throwable;

/**
 * Base class for every extension in this package.
 *
 * Extensions are identified by type and name; metadata (version, title, description,
 * author, etc.) is read from extension.json in the same directory as the extension class.
 * Concrete extensions must implement: configuration(), form(), and handle().
 *
 * @package JobMetric\Extension
 */
abstract class AbstractExtension
{
    use EventTrait;

    /**
     * Cached extension.json data per class.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $extensionDataCache = [];

    /**
     * Configure the extension.
     *
     * @param ExtensionCore $extension
     *
     * @return void
     */
    abstract public function configuration(ExtensionCore $extension): void;

    /**
     * Define the form for this extension's plugins.
     *
     * @return FormBuilder
     */
    abstract public function form(): FormBuilder;

    /**
     * Handle the extension logic with the given plugin data.
     *
     * @param array<string, mixed> $options
     *
     * @return string|null
     */
    abstract public function handle(array $options = []): ?string;

    /**
     * Install the extension
     *
     * @return void
     */
    protected function install(): void
    {
        //
    }

    /**
     * Uninstall the extension
     *
     * @return void
     */
    protected function uninstall(): void
    {
        //
    }

    /**
     * Upgrade the extension
     *
     * @return void
     */
    protected function upgrade(): void
    {
        //
    }

    /**
     * Path to extension.json (same directory as the extension class file).
     *
     * @return string
     */
    protected static function getExtensionJsonPath(): string
    {
        return dirname((new ReflectionClass(static::class))->getFileName()) . DIRECTORY_SEPARATOR . 'extension.json';
    }

    /**
     * Decoded extension.json data; cached per class. Keys: extension, name, version, title, multiple, priority,
     * depends, description, author, email, website, creationDate, copyright, license.
     *
     * @return array<string, mixed>
     */
    protected static function getExtensionData(): array
    {
        $class = static::class;
        if (isset(static::$extensionDataCache[$class])) {
            return static::$extensionDataCache[$class];
        }

        $path = static::getExtensionJsonPath();

        $data = [];
        if (is_file($path)) {
            $json = @file_get_contents($path);
            if ($json !== false) {
                $decoded = json_decode($json, true);
                $data = is_array($decoded) ? $decoded : [];
            }
        }

        static::$extensionDataCache[$class] = $data;

        return $data;
    }

    /**
     * Type of this extension (e.g. Module, PaymentMethod). From extension.json.
     *
     * @return string
     */
    public static function extension(): string
    {
        return (string) (static::getExtensionData()['extension'] ?? '');
    }

    /**
     * Short name/slug of this extension (e.g. Banner). From extension.json.
     *
     * @return string
     */
    public static function name(): string
    {
        return (string) (static::getExtensionData()['name'] ?? '');
    }

    /**
     * Extension version (semantic). From extension.json.
     *
     * @return string
     */
    public static function version(): string
    {
        return (string) (static::getExtensionData()['version'] ?? '1.0.0');
    }

    /**
     * Human-readable title (translation key or plain text). From extension.json.
     *
     * @return string
     */
    public static function title(): string
    {
        return (string) (static::getExtensionData()['title'] ?? '');
    }

    /**
     * True if this extension can have more than one plugin. From extension.json.
     *
     * @return bool
     */
    public static function multiple(): bool
    {
        return (bool) (static::getExtensionData()['multiple'] ?? false);
    }

    /**
     * Execution priority. Lower runs first. From extension.json.
     *
     * @return int
     */
    public static function priority(): int
    {
        return (int) (static::getExtensionData()['priority'] ?? 0);
    }

    /**
     * FQCNs of extensions this one depends on. From extension.json.
     *
     * @return array<int, string>
     */
    public static function depends(): array
    {
        $v = static::getExtensionData()['depends'] ?? [];

        return is_array($v) ? array_values($v) : [];
    }

    /**
     * Short description (translation key or plain text). From extension.json.
     *
     * @return string
     */
    public static function description(): string
    {
        return (string) (static::getExtensionData()['description'] ?? 'extension::base.extension.default_description');
    }

    /**
     * Author name. From extension.json.
     *
     * @return string|null
     */
    public static function author(): ?string
    {
        $v = static::getExtensionData()['author'] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * Author email. From extension.json.
     *
     * @return string|null
     */
    public static function email(): ?string
    {
        $v = static::getExtensionData()['email'] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * Author or extension website URL. From extension.json.
     *
     * @return string|null
     */
    public static function website(): ?string
    {
        $v = static::getExtensionData()['website'] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * Date when the extension was created. From extension.json.
     *
     * @return string|null
     */
    public static function creationDate(): ?string
    {
        $v = static::getExtensionData()['creationDate'] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * Copyright text. From extension.json.
     *
     * @return string|null
     */
    public static function copyright(): ?string
    {
        $v = static::getExtensionData()['copyright'] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * License name (e.g. MIT). From extension.json.
     *
     * @return string|null
     */
    public static function license(): ?string
    {
        $v = static::getExtensionData()['license'] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * Export extension metadata and form definition as an array.
     * Used by the installer and listing; structure aligns with extension.json fields plus form.
     *
     * @return array{
     *     extension: string,
     *     name: string,
     *     version: string,
     *     title: string,
     *     description: string,
     *     multiple: bool,
     *     priority: int,
     *     depends: array<int, string>,
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
            'priority'     => static::priority(),
            'depends'      => static::depends(),
            'author'       => static::author(),
            'email'        => static::email(),
            'website'      => static::website(),
            'creationDate' => static::creationDate(),
            'copyright'    => static::copyright(),
            'license'      => static::license(),
            'form'         => $this->form()->build()->toArray(),
        ];
    }
}
