<?php

use EvolutionCMS\EvoDirectoryEditor\Controllers\EvoDirectoryEditorController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/directory-editor/')
    ->middleware(['directory-editor-csrf', 'directory-editor-manager'])
    ->group(function () {
        Route::post('get-editor', [ EvoDirectoryEditorController::class, 'ajaxGetEditor' ]);
        Route::post('save-value', [ EvoDirectoryEditorController::class, 'ajaxSaveValue' ]);
    });