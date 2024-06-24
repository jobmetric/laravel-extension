<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;
use JobMetric\Extension\Events\ExtensionInstallEvent;
use JobMetric\Extension\Exceptions\ExtensionAlreadyInstalledException;
use JobMetric\Extension\Exceptions\ExtensionClassNameNotMatchException;
use JobMetric\Extension\Exceptions\ExtensionConfigFileNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionConfigurationNotMatchException;
use JobMetric\Extension\Exceptions\ExtensionDontHaveContractException;
use JobMetric\Extension\Exceptions\ExtensionDontHaveHandleMethodException;
use JobMetric\Extension\Exceptions\ExtensionFolderNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionRunnerNotFoundException;
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

        if (!isset($extension_information['extension']) ||
            !isset($extension_information['name']) ||
            !isset($extension_information['version']) ||
            !isset($extension_information['multiple'])) {
            throw new ExtensionConfigurationNotMatchException($extension, $name);
        }

        if (!file_exists(base_path("app/Extensions/$extension/$name/$name.php"))) {
            throw new ExtensionRunnerNotFoundException($extension, $name);
        }

        $namespace = "App\\Extensions\\$extension\\$name\\$name";

        // check class name
        if (!class_exists($namespace)) {
            throw new ExtensionClassNameNotMatchException($extension, $name);
        }

        // check class has implement ExtensionContract
        if (!in_array('JobMetric\Extension\Contracts\ExtensionContract', class_implements($namespace))) {
            throw new ExtensionDontHaveContractException($extension, $name);
        }

        if (method_exists($namespace, 'install')) {
            $namespace::install();
        }

        // create a new extension
        $extension_model = new ExtensionModel;

        $extension_model->extension = $extension;
        $extension_model->name = $name;
        $extension_model->info = $extension_information;

        $extension_model->save();

        event(new ExtensionInstallEvent($extension_model));
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
