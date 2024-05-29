<?php

namespace JobMetric\Extension;

use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class ExtensionServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-extension')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation();
    }
}
