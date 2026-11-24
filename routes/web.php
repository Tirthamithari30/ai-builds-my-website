<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessSourceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadedFileController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Business Management
    Route::resource('businesses', BusinessController::class);

    // Business Sources & Documents (Phase 3)
    Route::get('/businesses/{business}/sources', [BusinessSourceController::class, 'index'])->name('businesses.sources.index');
    Route::post('/businesses/{business}/sources', [BusinessSourceController::class, 'store'])->name('businesses.sources.store');
    Route::put('/sources/{source}', [BusinessSourceController::class, 'update'])->name('sources.update');
    Route::delete('/sources/{source}', [BusinessSourceController::class, 'destroy'])->name('sources.destroy');
    Route::post('/sources/{source}/retry', [BusinessSourceController::class, 'retry'])->name('sources.retry');

    // Uploaded Files (Phase 3)
    Route::post('/businesses/{business}/files', [UploadedFileController::class, 'store'])->name('businesses.files.store');
    Route::delete('/files/{file}', [UploadedFileController::class, 'destroy'])->name('files.destroy');
    Route::post('/files/{file}/retry', [UploadedFileController::class, 'retry'])->name('files.retry');
});
