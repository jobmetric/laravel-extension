<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use Throwable;

/**
 * Uninstall an extension by type and name (rolls back migrations, removes from DB, optionally deletes plugins).
 *
 * When extension or name are omitted, prompts interactively: first extension type (from config or free input),
 * then a choice from the list of installed extensions of that type. If none are installed or no type exists,
 * the user is informed. Uses the extension_uninstall() helper to perform the uninstall.
 */
class ExtensionUninstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:uninstall
        {extension? : Extension type (e.g. Module)}
        {name? : Extension name (e.g. Banner)}
        {--force-delete-plugin : Remove plugins associated with the extension when uninstalling}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall extension by type and name';

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

            $name = $this->askNameFromInstalled($extension);
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
            $name = $this->askNameFromInstalled($extension);
            if ($name === null) {
                return 0;
            }
        }

        $extension = Str::studly(trim($extension));
        $name = Str::studly(trim($name));
        $forceDeletePlugin = (bool) $this->option('force-delete-plugin');

        try {
            $result = extension_uninstall($extension, $name, $forceDeletePlugin);
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
     * Show the list of installed extensions for the given type and let the user choose one to uninstall.
     * If none are installed, a message is shown and null is returned.
     *
     * @param string $extension Extension type (e.g. Module).
     *
     * @return string|null The selected extension name, or null if nothing to uninstall.
     */
    private function askNameFromInstalled(string $extension): ?string
    {
        $formatType = Str::studly(trim($extension));
        $installed = ExtensionModel::where('extension', $formatType)->get();

        if ($installed->isEmpty()) {
            $this->info('No installed extensions for type [' . $formatType . '].');

            return null;
        }

        $choices = [];
        $nameByChoice = [];
        foreach ($installed as $model) {
            $name = $model->name ?? '';
            $title = $model->info['title'] ?? $name;
            $label = (is_string($title) && trim($title) !== '' ? trans($title) . ' (' . $name . ')' : $name);
            $choices[] = $label;
            $nameByChoice[$label] = $name;
        }

        $selected = $this->choice('Select extension to uninstall', $choices);

        return $nameByChoice[$selected] ?? $selected;
    }
}
