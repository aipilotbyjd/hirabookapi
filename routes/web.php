<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return 'test';
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
