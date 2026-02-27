<?php

namespace JobMetric\Extension\Commands\Tools\Generators;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JobMetric\Extension\Commands\Tools\Contracts\ExtensionToolGeneratorInterface;

class MigrationGenerator extends AbstractExtensionToolGenerator implements ExtensionToolGeneratorInterface
{
    /**
     * Generate a migration file for the extension.
     *
     * @param string $basePath
     * @param string $namespace
     * @param array $options
     * @param Command $command
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function generate(string $basePath, string $namespace, array $options, Command $command): int
    {
        $target = $options['target'] ?? '';
        if ($target === '') {
            $command->error('Target name (e.g. Post or create_posts_table) is required.');
            return 1;
        }

        $table = Str::snake(Str::plural(Str::studly($target)));
        $migrationName = 'create_' . $table . '_table';
        $force = (bool) ($options['force'] ?? false);

        $date = date('Y_m_d_His');
        $versionSlug = $this->getExtensionVersionSlug($basePath);
        $basename = $date . '__' . $versionSlug . '__' . $migrationName . '.php';

        $migrationDir = $basePath . DIRECTORY_SEPARATOR . 'migrations';
        File::ensureDirectoryExists($migrationDir);
        $file = $migrationDir . DIRECTORY_SEPARATOR . $basename;

        if (File::isFile($file) && ! $force) {
            $command->warn('Migration file already exists. Use --force to overwrite.');
            return 2;
        }

        $replace = ['table' => $table];
        $written = $this->writeStub($file, 'model.migration.stub', $replace, $command);

        return $written ? 0 : 3;
    }
}
