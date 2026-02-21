<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Route;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\Extension\Support\ExtensionRegistry;
use JobMetric\Extension\Support\ExtensionTypeRegistry;
use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;
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
            ->registerClass('Extension', Extension::class)
            ->registerClass('ExtensionType', ExtensionType::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('ExtensionTypeRegistry', ExtensionTypeRegistry::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('ExtensionRegistry', ExtensionRegistry::class, RegisterClassTypeEnum::SINGLETON());
    }

    /**
     * After register package
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function afterRegisterPackage(): void
    {
        // register route model bindings
        Route::model('jm_extension', ExtensionModel::class);
        Route::model('jm_plugin', PluginModel::class);

        // register extension types
        $registry = $this->app->make('ExtensionTypeRegistry');
        foreach (config('extension.types', []) as $type => $options) {
            $registry->register($type, is_array($options) ? $options : []);
        }
    }
}
