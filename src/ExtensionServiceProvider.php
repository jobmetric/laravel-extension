<?php

namespace JobMetric\Extension;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use JobMetric\BanIp\BanIp;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;
use JobMetric\Taxonomy\Models\Taxonomy as TaxonomyModel;
use JobMetric\Taxonomy\Models\TaxonomyPath;
use JobMetric\Taxonomy\Models\TaxonomyRelation;

class ExtensionServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     * @throws ViewFolderNotFoundException
     * @throws AssetFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-extension')
            ->hasAsset()
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

    /**
     * After register package
     *
     * @return void
     */
    public function afterRegisterPackage(): void
    {
        // Register model binding
        Route::model('jm_extension', ExtensionModel::class);
        Route::model('jm_plugin', PluginModel::class);
    }

    /**
     * After boot package
     *
     * @return void
     */
    public function afterBootPackage(): void
    {
        if (checkDatabaseConnection() && !app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->loadAddPlugin();
        }
    }

    /**
     * Load add plugin
     *
     * @return void
     */
    private function loadAddPlugin(): void
    {
        $extensionPath = app_path('Extensions');
        if (is_dir($extensionPath)) {
            $ds = DIRECTORY_SEPARATOR;
            $extensions = array_diff(scandir($extensionPath), ['..', '.']);

            foreach ($extensions as $extension) {
                $modules = array_diff(scandir($extensionPath . $ds . $extension), ['..', '.']);
                foreach ($modules as $module) {
                    $langFile = $extensionPath . $ds . $extension . $ds . $module . $ds . 'lang' . $ds . app()->getLocale() . $ds . 'extension.php';

                    if (!file_exists($langFile)) {
                        $langFile = $extensionPath . $ds . $extension . $ds . $module . $ds . "lang${$ds}en${$ds}extension.php";
                    }

                    if(file_exists($langFile)) {
                        $this->loadTranslationsFrom($extensionPath . $ds . $extension . $ds . $module . $ds . 'lang', 'extension-' . Str::kebab($extension) . '-' . Str::kebab($module));
                    }
                }
            }
        }
    }
}
