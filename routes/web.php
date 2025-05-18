<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SponsoredAdController;
use App\Http\Controllers\Admin\SponsoredAdController as AdminSponsoredAdController;

Route::get('/', function () {
    $quotes = [
        [
            'id' => 1,
            'quote' => 'The only way to do great work is to love what you do.',
            'author' => 'Steve Jobs'
        ],
        [
            'id' => 2,
            'quote' => 'Innovation distinguishes between a leader and a follower.',
            'author' => 'Steve Jobs'
        ],
        [
            'id' => 3,
            'quote' => 'Stay hungry, stay foolish.',
            'author' => 'Steve Jobs'
        ],
        [
            'id' => 4,
            'quote' => 'The future belongs to those who believe in the beauty of their dreams.',
            'author' => 'Eleanor Roosevelt'
        ],
    ];

    // Get a random quote
    $randomQuote = $quotes[array_rand($quotes)];

    return view('hirabook', ['quote' => $randomQuote]);
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Authentication Routes
    Route::get('login', [App\Http\Controllers\Admin\AdminController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Admin\AdminController::class, 'login'])->name('login.submit');
    Route::post('logout', [App\Http\Controllers\Admin\AdminController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['admin.auth'])->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

        // Settings
        Route::get('settings', [App\Http\Controllers\Admin\AdminController::class, 'showSettings'])->name('settings');
        Route::post('settings', [App\Http\Controllers\Admin\AdminController::class, 'updateSettings'])->name('settings.update');

        // Sponsored Ads Admin Routes
        Route::resource('sponsored-ads', SponsoredAdController::class);
    });
});


// Route::get('/generate', function () {
//     // Create personal access client
//     $output = [];
//     Artisan::call('passport:client', [
//         '--personal' => true,
//         '--no-interaction' => true
//     ]);
//     $clientOutput = Artisan::output();

//     // Generate encryption keys if they don't exist
//     Artisan::call('passport:keys', [
//         '--force' => true
//     ]);
//     $keysOutput = Artisan::output();

//     \Log::info('Passport installation output: ' . $clientOutput);

//     return [
//         'message' => 'Passport keys and client generated successfully',
//         'keys_output' => trim($keysOutput),
//         'client_output' => trim($clientOutput)
//     ];

// });

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

