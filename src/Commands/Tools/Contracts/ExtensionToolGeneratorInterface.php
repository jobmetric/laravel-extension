<?php

namespace JobMetric\Extension\Commands\Tools\Contracts;

use Illuminate\Console\Command;

interface ExtensionToolGeneratorInterface
{
    /**
     * Run the generator: create file(s) under extension path with given options.
     *
     * @param string $basePath Extension root path (e.g. app/Extensions/Module/Banner).
     * @param string $namespace Extension namespace (e.g. App\Extensions\Module\Banner\Core).
     * @param array<string, mixed> $options Options collected from interactive or CLI (tool-specific).
     * @param Command $command Running command instance (for output / ask).
     *
     * @return int 0 on success, non-zero on failure.
     */
    public function generate(string $basePath, string $namespace, array $options, Command $command): int;
}
