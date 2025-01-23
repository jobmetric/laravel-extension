<?php

use Illuminate\Support\Facades\Route;
use JobMetric\Extension\Http\Controllers\ExtensionController;
use JobMetric\Extension\Http\Controllers\PluginController;
use JobMetric\Panelio\Facades\Middleware;

/*
|--------------------------------------------------------------------------
| Laravel Extension Routes
|--------------------------------------------------------------------------
|
| All Route in Laravel Extension package
|
*/

Route::middleware(Middleware::getMiddlewares())->prefix('p/{panel}/{section}/extension/{type}')->namespace('JobMetric\Extension\Http\Controllers')->group(function () {
    // Extension
    Route::name('extension.')->group(function () {
        Route::get('/', [ExtensionController::class, 'index'])->name('index');
        Route::post('install', [ExtensionController::class, 'install'])->name('install');
        Route::post('uninstall', [ExtensionController::class, 'uninstall'])->name('uninstall');
        Route::post('delete', [ExtensionController::class, 'delete'])->name('delete');
    });

    // Plugin
    Route::options('extension/{jm_extension}/plugin', [PluginController::class, 'options'])->name('extension.plugin.options');
    Route::resource('extension.plugin', PluginController::class)->except(['show', 'destroy'])
        ->parameters([
            'extension' => 'jm_extension:id',
            'plugin' => 'jm_plugin:id'
        ]);
});
