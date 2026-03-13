<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use Throwable;

/**
 * Install an extension by type and name (runs migrations, creates default plugin, registers in DB).
 *
 * When extension or name are omitted, prompts interactively: first extension type (from config or free input),
 * then a choice from the list of not-yet-installed extensions of that type. If all are installed or none exist,
 * the user is informed. Uses the extension_install() helper to perform the install.
 */
class ExtensionInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:install
        {extension? : Extension type (e.g. Module)}
        {name? : Extension name (e.g. Banner)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install extension by type and name';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
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

            $name = $this->askNameFromUninstalled($extension);
            if ($name === null) {
                return 0;
            }
        } elseif ($extension === null) {
            $extension = $this->askExtension();
            if ($extension === null) {
                return 1;
            }
        }

        if ($name === null) {
            $name = $this->askNameFromUninstalled($extension);
            if ($name === null) {
                return 0;
            }
        }

        $extension = Str::studly(trim($extension));
        $name = Str::studly(trim($name));

        try {
            $result = extension_install($extension, $name);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $message = $result->message ?? '';
        if ($result->ok) {
            $this->info($message);

            return 0;
        }

        $this->error($message);

        return 1;
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
     * Show the list of not-yet-installed extensions for the given type and let the user choose one.
     * If none exist or all are already installed, a message is shown and null is returned.
     *
     * @param string $extension Extension type (e.g. Module).
     *
     * @return string|null The selected extension name, or null if nothing to install.
     */
    private function askNameFromUninstalled(string $extension): ?string
    {
        $formatType = Str::studly(trim($extension));
        $specs = Extension::getExtensionWithType($formatType);

        if ($specs === []) {
            $this->warn('No extensions found for type: ' . $formatType . '. Register extensions in ExtensionRegistry (e.g. config or namespace discovery).');

            return null;
        }

        $installedNamespaces = ExtensionModel::where('extension', $formatType)->pluck('namespace')->all();
        $uninstalled = array_values(array_filter($specs, function (array $spec) use ($installedNamespaces): bool {
            return ! in_array($spec['namespace'] ?? '', $installedNamespaces, true);
        }));

        if ($uninstalled === []) {
            $this->info('All extensions of type [' . $formatType . '] are already installed.');

            return null;
        }

        $choices = [];
        $nameByChoice = [];
        foreach ($uninstalled as $spec) {
            $name = $spec['name'] ?? '';
            $title = $spec['title'] ?? $name;
            $label = (is_string($title) && trim($title) !== '' ? trans($title) . ' (' . $name . ')' : $name);
            $choices[] = $label;
            $nameByChoice[$label] = $name;
        }

        $selected = $this->choice('Select extension to install', $choices);

        return $nameByChoice[$selected] ?? $selected;
    }
}
