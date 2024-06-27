<?php

namespace JobMetric\Extension;

use JobMetric\BanIp\BanIp;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class ExtensionServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-extension')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->registerCommand(Commands\ExtensionMakeCommand::class)
            ->registerCommand(Commands\ExtensionInstallCommand::class)
            ->registerCommand(Commands\ExtensionUninstallCommand::class)
            ->registerClass('Plugin', Plugin::class)
            ->registerClass('Extension', Extension::class);
    }

    public function afterBootPackage(): void
    {
        if (checkDatabaseConnection() && !app()->runningInConsole() && !app()->runningUnitTests()) {
            // get all active extensions
            // loops through each extension for load language files
        }
    }
}
