<?php

use Illuminate\Support\Facades\Route;
use JobMetric\Extension\Http\Controllers\ExtensionController;
use JobMetric\Panelio\Facades\Middleware;

/*
|--------------------------------------------------------------------------
| Laravel Extension Routes
|--------------------------------------------------------------------------
|
| All Route in Laravel Extension package
|
*/

// Extension
Route::prefix('p/{panel}/{section}/extension')->name('extension.')->namespace('JobMetric\Extension\Http\Controllers')->group(function () {
    Route::middleware(Middleware::getMiddlewares())->group(function () {
        Route::post('{type}/install', [ExtensionController::class, 'install'])->name('install');
        Route::options('{type}', [ExtensionController::class, 'options'])->name('options');
        Route::resource('{type}', ExtensionController::class)->except(['show', 'destroy'])->parameter('{type}', 'jm_extension:id');
    });
});
