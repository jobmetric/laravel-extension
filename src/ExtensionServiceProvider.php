<?php

namespace JobMetric\Extension;

use JobMetric\BanIp\BanIp;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
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
     * @throws ViewFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-extension')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->hasRoute()
            ->hasView()
            ->registerCommand(Commands\ExtensionMakeCommand::class)
            ->registerCommand(Commands\ExtensionInstallCommand::class)
            ->registerCommand(Commands\ExtensionUninstallCommand::class)
            ->registerClass('Plugin', Plugin::class)
            ->registerClass('Extension', Extension::class)
            ->registerClass('ExtensionType', ExtensionType::class);
    }

    public function afterBootPackage(): void
    {
        if (checkDatabaseConnection() && !app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->loadAddPlugin();
        }
    }

    private function loadAddPlugin(): void
    {
        $extensionPath = app_path('Extensions');
        if (is_dir($extensionPath)) {
            $extensions = array_diff(scandir($extensionPath), ['..', '.']);

            foreach ($extensions as $extension) {
                $modules = array_diff(scandir($extensionPath . '/' . $extension), ['..', '.']);
                foreach ($modules as $module) {
                    $langFile = $extensionPath . '/' . $extension . '/' . $module . '/lang/' . app()->getLocale() . '/extension.php';

                    if (!file_exists($langFile)) {
                        $langFile = $extensionPath . '/' . $extension . '/' . $module . '/lang/en/extension.php';
                    }

                    if(file_exists($langFile)) {
                        $this->loadTranslationsFrom($extensionPath . '/' . $extension . '/' . $module . '/lang', 'extension_'.$extension.'_'.$module);
                    }
                }
            }
        }
    }
}
