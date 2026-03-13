<?php

use JobMetric\Extension\Facades\Extension;
use JobMetric\PackageCore\Output\Response;

if (! function_exists('extension_install')) {
    /**
     * Install an extension by type and name (resolves namespace and calls Extension::install).
     *
     * @param string $extension Extension type (e.g. Module).
     * @param string $name      Extension name (e.g. Banner).
     *
     * @return Response
     * @throws Throwable
     */
    function extension_install(string $extension, string $name): Response
    {
        return Extension::install(Extension::namespaceFor($extension, $name));
    }
}

if (! function_exists('extension_uninstall')) {
    /**
     * Uninstall an extension by type and name (resolves namespace and calls Extension::uninstall).
     *
     * @param string $extension         Extension type (e.g. Module).
     * @param string $name              Extension name (e.g. Banner).
     * @param bool $force_delete_plugin When true, removes plugins associated with the extension.
     *
     * @return Response
     * @throws Throwable
     */
    function extension_uninstall(string $extension, string $name, bool $force_delete_plugin = false): Response
    {
        return Extension::uninstall(Extension::namespaceFor($extension, $name), $force_delete_plugin);
    }
}
