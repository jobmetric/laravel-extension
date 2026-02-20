<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JobMetric\Extension\Facades\Extension;
use JobMetric\PackageCore\Commands\ConsoleTools;
use Throwable;

class ExtensionInstallCommand extends Command
{
    use ConsoleTools;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:install
                {name : Extension name (e.g., Module/Banner). Type must be registered in ExtensionTypeRegistry.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install extension';

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
            $result = Extension::install($extension, $name);
        } catch (Throwable $e) {
            $this->message($e->getMessage(), 'error');

            return 1;
        }

        $this->message($result['message'], 'success');

        return 0;
    }
}
