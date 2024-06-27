<?php

namespace JobMetric\Extension\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JobMetric\Extension\Enums\ExtensionTypeEnum;
use JobMetric\PackageCore\Commands\ConsoleTools;

class ExtensionMakeCommand extends Command
{
    use ConsoleTools;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:make
                {name : Extension name (e.g., Module/Banner) part one of the following types in <info>JobMetric\Extension\Enums\ExtensionTypeEnum</info>}
                {--f|full : Create full extension}
                {--m|multiple : Create multiple extension}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make extension for Laravel';

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

        if ($this->isDir('app/Extensions/' . $extension . '/' . $name)) {
            $this->message('Module already exists.', 'error');

            return 2;
        }

        if (!in_array($extension, ExtensionTypeEnum::values())) {
            $this->message('Invalid extension type.', 'error');

            return 3;
        }

        // make extension
        $is_full = $this->option('full');
        if ($is_full) {
            $content_extension = $this->getStub(__DIR__ . '/stub/extension-full', [
                'extension' => $extension,
                'name' => $name,
            ]);
        } else {
            $content_extension = $this->getStub(__DIR__ . '/stub/extension', [
                'extension' => $extension,
                'name' => $name,
            ]);
        }

        $path = base_path('app/Extensions/' . $extension . '/' . $name);

        if (!$this->isDir($path)) {
            $this->makeDir($path);
        }

        $this->putFile($path . '/' . $name . '.php', $content_extension);

        $this->message('Extension <options=bold>[' . $extension . '/' . $name . '/' . $name . '.php]</> created successfully.', 'success');

        // make extension.json
        $is_multiple = $this->option('multiple');
        $content_json = $this->getStub(__DIR__ . '/stub/extension', [
            'extension' => $extension,
            'name' => $name,
            'multiple' => $is_multiple ? 'true' : 'false',
            'date' => date('Y-m-d H:i:s'),
        ], '.json.stub');

        $this->putFile($path . '/extension.json', $content_json);

        $this->message('Extension <options=bold>[' . $extension . '/' . $name . '/extension.json]</> created successfully.', 'success');

        // make lang
        $path = base_path('app/Extensions/' . $extension . '/' . $name . '/lang/en');

        if (!$this->isDir($path)) {
            $this->makeDir($path);
        }

        $content_lang = $this->getStub(__DIR__ . '/stub/lang', [
            'extension' => $extension,
            'name' => $name,
        ]);

        $this->putFile($path . '/extension.php', $content_lang);

        $this->message('Extension <options=bold>[' . $extension . '/' . $name . '/lang/en/extension.php]</> created successfully.', 'success');

        return 0;
    }
}
