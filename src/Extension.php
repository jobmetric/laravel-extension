<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;
use JobMetric\Barcode\Events\ExtensionInstallEvent;
use JobMetric\Barcode\Exceptions\ExtensionAlreadyInstalledException;
use JobMetric\Barcode\Exceptions\ExtensionConfigFileNotFoundException;
use JobMetric\Barcode\Exceptions\ExtensionFolderNotFoundException;
use JobMetric\Barcode\Exceptions\ExtensionRunnerNotFoundException;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use Throwable;

class Extension
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Setting instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Extension installer
     *
     * @param string $extension
     * @param string $name
     *
     * @return void
     * @throws Throwable
     */
    public function install(string $extension, string $name): void
    {
        if (ExtensionModel::ExtensionName($extension, $name)->exists()) {
            throw new ExtensionAlreadyInstalledException($extension, $name);
        }

        if (!is_dir(base_path("app/Extensions/$extension/$name"))) {
            throw new ExtensionFolderNotFoundException($extension, $name);
        }

        if (!file_exists(base_path("app/Extensions/$extension/$name/extension.json"))) {
            throw new ExtensionConfigFileNotFoundException($extension, $name);
        }

        $extension_information = json_decode(file_get_contents(base_path("app/Extensions/$extension/$name/extension.json")), true);

        // check information in extension.json

        if (!file_exists(base_path("app/Extensions/$extension/$name/$name.json"))) {
            throw new ExtensionRunnerNotFoundException($extension, $name);
        }

        $namespace = "App\\Extensions\\$extension\\$name\\$name";
        if(method_exists($namespace, 'install')) {
            $namespace::install();
        }

        // create a new extension
        $extension = new ExtensionModel;

        $extension->extension = $extension;
        $extension->name = $name;
        $extension->info = $extension_information;

        $extension->save();

        event(new ExtensionInstallEvent($extension));
    }

    /**
     * Extension uninstaller
     *
     * @param string $extension
     * @param string $name
     *
     * @return void
     */
    public function uninstall(string $extension, string $name): void
    {
        // check if the extension is installed
        // if installed, then run the extension uninstaller
    }

    /**
     * Extension updater
     *
     * @param string $extension
     * @param string $name
     *
     * @return void
     */
    public function update(string $extension, string $name): void
    {
        // check the version in extension.json local and remote repository
        // if the local version is lower than the remote version then run $this->download($path)
    }

    /**
     * Extension installer with zip
     *
     * @param string $path
     * @param bool $delete_file
     *
     * @return void
     */
    public function installZip(string $path, bool $delete_file = false): void
    {
        // unzip file
        // read file extension.json for get extension and name
        // create folder in app/Extensions/{extension}/{name}
        // copy all files to app/Extensions/{extension}/{name}
        // run $this->install($extension, $name)
        // delete zip file if $delete_file is true
    }

    /**
     * Extension downloader
     *
     * @param string $path
     *
     * @return void
     */
    public function download(string $path): void
    {
        // download file
        // run $this->installZip($path, true)
    }

    /**
     * Extension uploader
     *
     * @param string $path
     *
     * @return void
     */
    public function upload(string $path): void
    {
        // upload file
        // run $this->installZip($path, true)
    }
}
