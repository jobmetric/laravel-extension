<?php

namespace JobMetric\Extension\Commands\Tools\Generators;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Commands\Tools\Contracts\ExtensionToolGeneratorInterface;

class ViewGenerator extends AbstractExtensionToolGenerator implements ExtensionToolGeneratorInterface
{
    /**
     * Generate a new view file for the extension.
     *
     * @param string $basePath
     * @param string $namespace
     * @param array $options
     * @param Command $command
     *
     * @return int
     */
    public function generate(string $basePath, string $namespace, array $options, Command $command): int
    {
        $target = $options['target'] ?? '';
        if ($target === '') {
            $command->error('Target name (e.g. welcome or pages.dashboard) is required.');
            return 1;
        }

        $viewName = Str::replace('.', DIRECTORY_SEPARATOR, $target);
        $force = (bool) ($options['force'] ?? false);

        $viewDir = $basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        $file = $viewDir . DIRECTORY_SEPARATOR . $viewName . '.blade.php';

        if (File::isFile($file) && ! $force) {
            $command->warn('View file already exists. Use --force to overwrite.');
            return 2;
        }

        File::ensureDirectoryExists(dirname($file));
        $content = "<div>\n    {{-- " . $viewName . " --}}\n</div>\n";
        File::put($file, $content);
        $command->info('Created: ' . $file);

        return 0;
    }
}
