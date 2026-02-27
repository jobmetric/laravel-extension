<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use JobMetric\Extension\Facades\ExtensionNamespaceRegistry as FacadeExtensionNamespaceRegistry;
use JobMetric\Extension\Facades\ExtensionTypeRegistry as FacadeExtensionTypeRegistry;
use JobMetric\Extension\Kernel\ExtensionKernel;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Models\Plugin as PluginModel;
use JobMetric\Extension\Support\ExtensionNamespaceRegistry;
use JobMetric\Extension\Support\ExtensionRegistry;
use JobMetric\Extension\Support\ExtensionTypeRegistry;
use JobMetric\Extension\Support\InstalledExtensionsFile;
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
            ->registerCommand(Commands\ExtensionMake::class)
            ->registerCommand(Commands\ExtensionMakeTools::class)
            ->registerCommand(Commands\ExtensionInstallCommand::class)
            ->registerCommand(Commands\ExtensionUninstallCommand::class)
            ->registerClass('Plugin', Plugin::class)
            ->registerClass('Extension', Extension::class)
            ->registerClass('ExtensionKernel', ExtensionKernel::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('ExtensionNamespaceRegistry', ExtensionNamespaceRegistry::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('ExtensionTypeRegistry', ExtensionTypeRegistry::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('ExtensionRegistry', ExtensionRegistry::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('InstalledExtensionsFile', InstalledExtensionsFile::class, RegisterClassTypeEnum::SINGLETON());
    }

    /**
     * After register package
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function afterRegisterPackage(): void
    {
        // register extension default namespace
        FacadeExtensionNamespaceRegistry::register(appNamespace() . "Extensions");

        // register extension types
        foreach (config('extension.types', []) as $type => $options) {
            FacadeExtensionTypeRegistry::register($type, is_array($options) ? $options : []);
        }

        /** @var ExtensionKernel $kernel */
        $kernel = $this->app->make('ExtensionKernel');

        App::booting(function () use ($kernel) {
            $kernel->discover()->loadInstalledExtensions()->registerExtensions();
        });

        App::booted(function () use ($kernel) {
            if (!$this->app->bound('db')) {
                return;
            }

            try {
                // register route model bindings
                Route::model('jm_extension', ExtensionModel::class);
                Route::model('jm_plugin', PluginModel::class);

                $kernel->upgradeExtensions();
                $kernel->bootExtensions($this, fn (array $paths, $groups) => $this->publishes($paths, $groups));
            } catch (QueryException $e) {
                // Table may not exist yet (migrations not run); e.g. during composer dump-autoload / package:discover
            }
        });
    }
}
