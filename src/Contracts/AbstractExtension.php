<?php

namespace JobMetric\Extension\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use JobMetric\Extension\Events\ExtensionMigrationsRollbackEvent;
use JobMetric\Extension\Events\ExtensionMigrationsRunEvent;
use JobMetric\Extension\Kernel\EventTrait;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Models\ExtensionMigration;
use JobMetric\Form\FormBuilder;
use ReflectionClass;
use Throwable;

/**
 * Base class for all extensions. Type and name identify the extension; metadata is read from
 * extension.json in the same directory as the extension class. Implement configuration(), form(), and handle().
 *
 * @package JobMetric\Extension
 */
abstract class AbstractExtension
{
    use EventTrait;

    /**
     * Cache for extension.json data to avoid repeated file reads. Keyed by class name, value is decoded JSON array.
     *
     * @var array<string, array<string, mixed>> Cached extension.json per class.
     */
    protected static array $extensionDataCache = [];

    /**
     * Extension base path (directory of the class file).
     *
     * @var string|null
     */
    private ?string $basePath = null;

    /**
     * Configure the extension (register config, views, bindings, etc.).
     *
     * @param ExtensionCore $extension
     *
     * @return void
     */
    abstract public function configuration(ExtensionCore $extension): void;

    /**
     * Build the form definition for this extension's plugins.
     *
     * @return FormBuilder
     */
    abstract public function form(): FormBuilder;

    /**
     * Handle extension logic with the given plugin options.
     *
     * @param array<string, mixed> $options
     *
     * @return string|null
     */
    abstract public function handle(array $options = []): ?string;

    /**
     * Directory containing the extension class file.
     * Cached after first access since used multiple times during migration/upgrade.
     *
     * @return string
     */
    private function getBasePath(): string
    {
        if ($this->basePath === null) {
            $this->basePath = dirname((new ReflectionClass($this))->getFileName());
        }

        return $this->basePath;
    }

    /**
     * Path to the migrations directory for this extension.
     * Assumes migrations are in a 'migrations' subdirectory of the extension class file directory.
     *
     * @return string
     */
    private function getMigrationsPath(): string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'migrations';
    }

    /**
     * Builder scoped to this extension's rows in extension_migrations.
     *
     * @return Builder
     */
    private function migrationQuery(): Builder
    {
        return ExtensionMigration::query()->where('extension', static::extension())->where('name', static::name());
    }

    /**
     * Basenames of migrations already recorded.
     *
     * @return array<string, int>
     */
    private function getAlreadyRunMigrations(): array
    {
        return $this->migrationQuery()->pluck('migration')->flip()->all();
    }

    /**
     * Parse date and version from migration filename (date__version__name.php).
     *
     * @return array{date: string, version: string}|null
     */
    private function parseMigrationBasename(string $basename): ?array
    {
        $parts = explode('__', pathinfo($basename, PATHINFO_FILENAME), 3);
        if (count($parts) < 3) {
            return null;
        }

        return [
            'date'    => $parts[0],
            'version' => str_replace('_', '.', $parts[1]),
        ];
    }

    /**
     * Collect migration files to run: within version range, not already run, sorted by date.
     *
     * @param array<string, int> $alreadyRun
     * @param string|null $minVersionExclusive Exclude if file version <= this (null = no lower bound).
     * @param string $maxVersionInclusive      Exclude if file version > this.
     *
     * @return list<array{path: string, basename: string, date: string}>
     */
    private function collectMigrationsToRun(
        string $migrationsPath,
        array $alreadyRun,
        ?string $minVersionExclusive,
        string $maxVersionInclusive
    ): array {
        $files = glob($migrationsPath . DIRECTORY_SEPARATOR . '*.php') ?: [];

        $toRun = [];
        foreach ($files as $file) {
            $basename = basename($file);
            $info = $this->parseMigrationBasename($basename);
            if ($info === null || isset($alreadyRun[$basename])) {
                continue;
            }

            if ($minVersionExclusive !== null && version_compare($info['version'], $minVersionExclusive, '<=')) {
                continue;
            }

            if (version_compare($info['version'], $maxVersionInclusive, '>')) {
                continue;
            }

            $toRun[] = ['path' => $file, 'basename' => $basename, 'date' => $info['date']];
        }

        usort($toRun, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $toRun;
    }

    /**
     * Load migration file and run up().
     *
     * Assumes migration file returns an object with up() method (like Laravel migrations).
     * Does not record migration in database; caller should do that after successful run.
     *
     * @param string $path
     *
     * @return void
     */
    private function executeMigrationUp(string $path): void
    {
        $migration = require $path;
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        }
    }

    /**
     * Insert one row into extension_migrations for this extension.
     *
     * Assumes migration was successfully run; caller should call after executeMigrationUp().
     *
     * @param string $basename
     *
     * @return void
     */
    private function recordMigration(string $basename): void
    {
        ExtensionMigration::query()->create([
            'extension' => static::extension(),
            'name'      => static::name(),
            'migration' => $basename,
        ]);
    }

    /**
     * Run down() on migration file if it exists.
     *
     * Assumes migration file returns an object with down() method (like Laravel migrations).
     * Does not remove record from database; caller should do that after successful run.
     *
     * @param string $path
     *
     * @return void
     */
    private function rollbackMigrationFile(string $path): void
    {
        if (! is_file($path)) {
            return;
        }
        $migration = require $path;
        if (is_object($migration) && method_exists($migration, 'down')) {
            $migration->down();
        }
    }

    /**
     * Run migrations up to current version; record each in extension_migrations; dispatch event after.
     * File format: date__version__name.php (date Y_m_d_His, version e.g. 1_0_0, name any).
     *
     * @return void
     */
    public function install(): void
    {
        $migrationsPath = $this->getMigrationsPath();
        if (! is_dir($migrationsPath)) {
            Event::dispatch(new ExtensionMigrationsRunEvent($this, []));

            return;
        }

        $alreadyRun = $this->getAlreadyRunMigrations();
        $toRun = $this->collectMigrationsToRun($migrationsPath, $alreadyRun, null, static::version());

        $run = [];
        foreach ($toRun as $item) {
            $this->executeMigrationUp($item['path']);
            $this->recordMigration($item['basename']);
            $run[] = $item['basename'];
        }

        Event::dispatch(new ExtensionMigrationsRunEvent($this, $run));
    }

    /**
     * Rollback all migrations for this extension (reverse order), remove records, dispatch event.
     *
     * @return void
     */
    protected function uninstall(): void
    {
        $migrationsPath = $this->getMigrationsPath();
        $rows = $this->migrationQuery()->orderByDesc('id')->get();

        $rollback = [];
        foreach ($rows as $row) {
            $this->rollbackMigrationFile($migrationsPath . DIRECTORY_SEPARATOR . $row->migration);
            $row->delete();
            $rollback[] = $row->migration;
        }

        Event::dispatch(new ExtensionMigrationsRollbackEvent($this, $rollback));
    }

    /**
     * Max version among migration filenames recorded in extension_migrations for this extension; 0.0.0 if none.
     *
     * @return string
     */
    private function getStoredVersion(): string
    {
        $max = '0.0.0';
        foreach ($this->migrationQuery()->pluck('migration') as $basename) {
            $info = $this->parseMigrationBasename($basename);
            if ($info !== null && version_compare($info['version'], $max, '>')) {
                $max = $info['version'];
            }
        }

        return $max;
    }

    /**
     * Sync migrations with extension.json version: if current &gt; stored run new migrations; if current &lt; stored
     * rollback. Call before boot.
     *
     * Note: this does not handle all cases (e.g. if user downgrades to a version with different migration files); it
     * just handles the common case of running new migrations on upgrade and rolling back on downgrade. More complex
     * cases would require more complex logic and are not currently handled.
     *
     * @return void
     */
    public function upgrade(): void
    {
        $current = static::version();
        $stored = $this->getStoredVersion();
        if (version_compare($current, $stored, '>')) {
            $this->runUpgradeMigrations($stored, $current);
        }
        else if (version_compare($current, $stored, '<')) {
            $this->rollbackDowngradeMigrations($current, $stored);
        }
    }

    /**
     * Run migrations with version in (storedVersion, currentVersion].
     *
     * Assumes migration files are named with version in format date__version__name.php, where version is e.g. 1_0_0.
     *
     * Checks migration files in the migrations directory, filters by version range and already run, sorts by date,
     * runs up() on each, records in database, dispatches event with list of run migrations.
     *
     * Note: does not handle all edge cases (e.g. if user upgrades to a version with different migration files); it
     * just handles the common case of running new migrations on upgrade. More complex cases would require more complex
     * logic and are not currently handled.
     *
     * @param string $storedVersion  Version currently recorded in database (max version among recorded migrations).
     * @param string $currentVersion Version from extension.json.
     *
     * @return void
     */
    private function runUpgradeMigrations(string $storedVersion, string $currentVersion): void
    {
        $migrationsPath = $this->getMigrationsPath();
        if (! is_dir($migrationsPath)) {
            return;
        }

        $alreadyRun = $this->getAlreadyRunMigrations();
        $toRun = $this->collectMigrationsToRun($migrationsPath, $alreadyRun, $storedVersion, $currentVersion);

        $run = [];
        foreach ($toRun as $item) {
            $this->executeMigrationUp($item['path']);
            $this->recordMigration($item['basename']);
            $run[] = $item['basename'];
        }

        if ($run !== []) {
            Event::dispatch(new ExtensionMigrationsRunEvent($this, $run));
        }
    }

    /**
     * Rollback migrations with version in (currentVersion, storedVersion].
     *
     * Checks migration files in the migrations directory, filters by version range, sorts by date desc, runs down() on
     * each, removes record from database, dispatches event with list of rolled back migrations.
     *
     * Note: does not handle all edge cases (e.g. if user downgrades to a version with different migration files); it
     * just handles the common case of rolling back migrations on downgrade. More complex cases would require more
     * complex logic and are not currently handled.
     *
     * @param string $currentVersion Version from extension.json.
     * @param string $storedVersion  Version currently recorded in database (max version among recorded migrations).
     *
     * @return void
     */
    private function rollbackDowngradeMigrations(string $currentVersion, string $storedVersion): void
    {
        $migrationsPath = $this->getMigrationsPath();
        $rows = $this->migrationQuery()->orderByDesc('id')->get();
        $rollback = [];
        foreach ($rows as $row) {
            $info = $this->parseMigrationBasename($row->migration);
            if ($info === null || version_compare($info['version'], $currentVersion, '<=') || version_compare($info['version'], $storedVersion, '>')) {
                continue;
            }
            $this->rollbackMigrationFile($migrationsPath . DIRECTORY_SEPARATOR . $row->migration);
            $row->delete();
            $rollback[] = $row->migration;
        }
        if ($rollback !== []) {
            Event::dispatch(new ExtensionMigrationsRollbackEvent($this, $rollback));
        }
    }

    /**
     * Absolute path to extension.json (next to the extension class).
     *
     * Assumes extension.json is in the same directory as the extension class file.
     * Does not check if file exists; caller should handle that. Cached by getExtensionData() to avoid repeated file
     * reads.
     *
     * @return string
     */
    protected static function getExtensionJsonPath(): string
    {
        return dirname((new ReflectionClass(static::class))->getFileName()) . DIRECTORY_SEPARATOR . 'extension.json';
    }

    /**
     * Decoded extension.json; cached per class.
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
     * Extension type (e.g. Module, PaymentMethod).
     *
     * @return string
     */
    public static function extension(): string
    {
        return (string) (static::getExtensionData()['extension'] ?? '');
    }

    /**
     * Extension short name (e.g. Banner).
     *
     * @return string
     */
    public static function name(): string
    {
        return (string) (static::getExtensionData()['name'] ?? '');
    }

    /**
     * Semantic version (default 1.0.0).
     *
     * @return string
     */
    public static function version(): string
    {
        return (string) (static::getExtensionData()['version'] ?? '1.0.0');
    }

    /**
     * Human-readable title.
     *
     * @return string
     */
    public static function title(): string
    {
        return (string) (static::getExtensionData()['title'] ?? '');
    }

    /**
     * Whether multiple plugin instances are allowed.
     *
     * @return bool
     */
    public static function multiple(): bool
    {
        return (bool) (static::getExtensionData()['multiple'] ?? false);
    }

    /**
     * Load order priority (lower runs first).
     *
     * @return int
     */
    public static function priority(): int
    {
        return (int) (static::getExtensionData()['priority'] ?? 0);
    }

    /**
     * FQCNs of extensions this one depends on.
     *
     * @return array<int, string>
     */
    public static function depends(): array
    {
        $v = static::getExtensionData()['depends'] ?? [];

        return is_array($v) ? array_values($v) : [];
    }

    /**
     * Short description (translation key or text).
     *
     * @return string
     */
    public static function description(): string
    {
        return (string) (static::getExtensionData()['description'] ?? 'extension::base.extension.default_description');
    }

    /**
     * Author name.
     *
     * @return string|null
     */
    public static function author(): ?string
    {
        return static::getExtensionDataStringOrNull('author');
    }

    /**
     * Author email.
     *
     * @return string|null
     */
    public static function email(): ?string
    {
        return static::getExtensionDataStringOrNull('email');
    }

    /**
     * Website URL.
     *
     * @return string|null
     */
    public static function website(): ?string
    {
        return static::getExtensionDataStringOrNull('website');
    }

    /**
     * Creation date.
     *
     * @return string|null
     */
    public static function creationDate(): ?string
    {
        return static::getExtensionDataStringOrNull('creationDate');
    }

    /**
     * Copyright.
     *
     * @return string|null
     */
    public static function copyright(): ?string
    {
        return static::getExtensionDataStringOrNull('copyright');
    }

    /**
     * License name.
     *
     * @return string|null
     */
    public static function license(): ?string
    {
        return static::getExtensionDataStringOrNull('license');
    }

    /**
     * String value for key from extension.json, or null if missing/empty.
     *
     * @return string|null
     */
    protected static function getExtensionDataStringOrNull(string $key): ?string
    {
        $v = static::getExtensionData()[$key] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }

    /**
     * Metadata and form as array (for installer/listing).
     *
     * @return array{extension: string, name: string, version: string, title: string, description: string, multiple:
     *                          bool, priority: int, depends: array<int, string>, author?: string|null, email?:
     *                          string|null, website?: string|null, creationDate?: string|null, copyright?:
     *                          string|null, license?: string|null, form: array<string, mixed>}
     * @throws Throwable
     */
    public function toArray(): array
    {
        $data = static::getExtensionData();

        return [
            'extension'    => (string) ($data['extension'] ?? ''),
            'name'         => (string) ($data['name'] ?? ''),
            'version'      => (string) ($data['version'] ?? '1.0.0'),
            'title'        => (string) ($data['title'] ?? ''),
            'description'  => (string) ($data['description'] ?? 'extension::base.extension.default_description'),
            'multiple'     => (bool) ($data['multiple'] ?? false),
            'priority'     => (int) ($data['priority'] ?? 0),
            'depends'      => is_array($data['depends'] ?? null) ? array_values($data['depends']) : [],
            'author'       => static::dataStringOrNull($data, 'author'),
            'email'        => static::dataStringOrNull($data, 'email'),
            'website'      => static::dataStringOrNull($data, 'website'),
            'creationDate' => static::dataStringOrNull($data, 'creationDate'),
            'copyright'    => static::dataStringOrNull($data, 'copyright'),
            'license'      => static::dataStringOrNull($data, 'license'),
            'form'         => $this->form()->build()->toArray(),
        ];
    }

    /** Null if key missing or empty. @param array<string, mixed> $data */
    protected static function dataStringOrNull(array $data, string $key): ?string
    {
        $v = $data[$key] ?? null;

        return $v === '' || $v === null ? null : (string) $v;
    }
}
