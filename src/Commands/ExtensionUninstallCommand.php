<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JobMetric\Extension\Enums\ExtensionTypeEnum;
use JobMetric\Extension\Facades\Extension;
use JobMetric\PackageCore\Commands\ConsoleTools;
use Throwable;

class ExtensionUninstallCommand extends Command
{
    use ConsoleTools;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:uninstall
                {name : Extension name (e.g., Module/Banner) part one of the following types in <info>JobMetric\Extension\Enums\ExtensionTypeEnum</info>}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall extension';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $scope = $this->argument('name');

        $parts = explode('/', $scope);

        if (count($parts) != 2) {
            $this->message('Invalid extension name.', 'error');

            return 1;
        }

        $extension = Str::studly($parts[0]);
        $name = Str::studly($parts[1]);

        try {
            $result = Extension::uninstall($extension, $name);
        } catch (Throwable $e) {
            $this->message($e->getMessage(), 'error');

            return 1;
        }

        $this->message($result['message'], 'success');

        return 0;
    }
}
