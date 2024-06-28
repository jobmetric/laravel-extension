<?php

use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Facades\Plugin;

if (!function_exists('extension_install')) {
    /**
     * Install extension
     *
     * @param string $extension
     * @param string $name
     *
     * @return array
     * @throws Throwable
     */
    function extension_install(string $extension, string $name): array
    {
        return Extension::install($extension, $name);
    }
}

if (!function_exists('extension_uninstall')) {
    /**
     * Uninstall extension
     *
     * @param string $extension
     * @param string $name
     * @param bool $force_delete_plugin
     *
     * @return array
     * @throws Throwable
     */
    function extension_uninstall(string $extension, string $name, bool $force_delete_plugin = false): array
    {
        return Extension::uninstall($extension, $name, $force_delete_plugin);
    }
}

if (!function_exists('extension_update')) {
    /**
     * Update extension
     *
     * @param string $extension
     * @param string $name
     *
     * @return array
     * @throws Throwable
     */
    function extension_update(string $extension, string $name): array
    {
        return Extension::update($extension, $name);
    }
}

if (!function_exists('plugin_add')) {
    /**
     * Add plugin
     *
     * @param string $extension
     * @param string $name
     * @param array $fields
     *
     * @return array
     * @throws Throwable
     */
    function plugin_add(string $extension, string $name, array $fields): array
    {
        return Plugin::add($extension, $name, $fields);
    }
}

if (!function_exists('plugin_edit')) {
    /**
     * Edit plugin
     *
     * @param int $plugin_id
     * @param array $fields
     *
     * @return array
     * @throws Throwable
     */
    function plugin_edit(int $plugin_id, array $fields): array
    {
        return Plugin::edit($plugin_id, $fields);
    }
}

if (!function_exists('plugin_delete')) {
    /**
     * Delete plugin
     *
     * @param int $plugin_id
     *
     * @return array
     * @throws Throwable
     */
    function plugin_delete(int $plugin_id): array
    {
        return Plugin::delete($plugin_id);
    }
}

if (!function_exists('plugin_run')) {
    /**
     * Run plugin
     *
     * @param int $plugin_id
     *
     * @return string|null
     * @throws Throwable
     */
    function plugin_run(int $plugin_id): ?string
    {
        return Plugin::run($plugin_id);
    }
}
