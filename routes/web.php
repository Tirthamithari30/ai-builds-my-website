<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessSourceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FactVerificationController;
use App\Http\Controllers\UploadedFileController;
use App\Http\Controllers\WebsiteEditorController;
use App\Http\Controllers\WebsiteGenerationController;
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

    // Fact Verification & Review (Phase 4)
    Route::get('/businesses/{business}/facts', [FactVerificationController::class, 'index'])->name('businesses.facts.index');
    Route::post('/facts/{fact}/confirm', [FactVerificationController::class, 'confirm'])->name('facts.confirm');
    Route::put('/facts/{fact}/edit-confirm', [FactVerificationController::class, 'editAndConfirm'])->name('facts.edit-confirm');
    Route::post('/facts/{fact}/reject', [FactVerificationController::class, 'reject'])->name('facts.reject');
    Route::post('/facts/{fact}/outdated', [FactVerificationController::class, 'markOutdated'])->name('facts.outdated');
    Route::post('/businesses/{business}/facts/resolve-conflict', [FactVerificationController::class, 'resolveConflict'])->name('businesses.facts.resolve-conflict');
    Route::post('/businesses/{business}/facts/manual', [FactVerificationController::class, 'addManual'])->name('businesses.facts.manual');
    Route::post('/businesses/{business}/facts/reextract', [FactVerificationController::class, 'reextract'])->name('businesses.facts.reextract');

    // Website Generation Engine (Phase 5)
    Route::get('/businesses/{business}/website/setup', [WebsiteGenerationController::class, 'setup'])->name('businesses.website.setup');
    Route::post('/businesses/{business}/website/generate', [WebsiteGenerationController::class, 'generate'])->name('businesses.website.generate');
    Route::get('/websites/{website}/preview', [WebsiteGenerationController::class, 'preview'])->name('websites.preview');
    Route::get('/websites/{website}/preview-frame', [WebsiteGenerationController::class, 'previewFrame'])->name('websites.preview.frame');
    Route::get('/websites/{website}/versions', [WebsiteGenerationController::class, 'versions'])->name('websites.versions');
    Route::post('/websites/{website}/versions/{version}/restore', [WebsiteGenerationController::class, 'restoreVersion'])->name('websites.versions.restore');

    // Visual Website Editor (Phase 6)
    Route::get('/websites/{website}/edit', [WebsiteEditorController::class, 'edit'])->name('websites.edit');
    Route::post('/websites/{website}/sections', [WebsiteEditorController::class, 'addSection'])->name('websites.sections.add');
    Route::put('/sections/{section}', [WebsiteEditorController::class, 'updateSection'])->name('sections.update');
    Route::delete('/sections/{section}', [WebsiteEditorController::class, 'deleteSection'])->name('sections.delete');
    Route::post('/sections/{section}/toggle-visibility', [WebsiteEditorController::class, 'toggleVisibility'])->name('sections.toggle-visibility');
    Route::post('/websites/{website}/sections/reorder', [WebsiteEditorController::class, 'reorderSections'])->name('websites.sections.reorder');
    Route::post('/sections/{section}/regenerate-preview', [WebsiteEditorController::class, 'regenerateSectionPreview'])->name('sections.regenerate-preview');
    Route::post('/sections/{section}/apply-regeneration', [WebsiteEditorController::class, 'applySectionRegeneration'])->name('sections.apply-regeneration');
    Route::put('/websites/{website}/design', [WebsiteEditorController::class, 'updateDesign'])->name('websites.design.update');
    Route::post('/websites/{website}/save-all', [WebsiteEditorController::class, 'saveAll'])->name('websites.save-all');
});
