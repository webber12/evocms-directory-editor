<?php

use EvolutionCMS\EvoDirectoryEditor\Controllers\EvoDirectoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/directory-editor/')
    ->middleware(['directory-editor-csrf', 'directory-editor-manager'])
    ->group(function () {
        Route::post('get-editor', [ EvoDirectoryController::class, 'ajaxGetEditor' ]);
        Route::post('save-value', [ EvoDirectoryController::class, 'ajaxSaveValue' ]);
    });