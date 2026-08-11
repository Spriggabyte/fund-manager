<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Internal PDF render target hit by Puppeteer. It bypasses session auth (the
// headless browser has no session) but is protected by a short-lived signed
// URL so fund fact-sheet HTML is never publicly reachable by id.
Route::get('/internal/funds/{fund}/pdf-view', [FundController::class, 'internalPdfView'])
    ->middleware('signed')
    ->name('funds.internal.pdf-view');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('funds', FundController::class);

    // Fact sheet view
    Route::get('funds/{fund}/fact-sheet', [FundController::class, 'factSheet'])
        ->name('funds.fact-sheet');

    // PDF export — dispatches a queued render, then polls + downloads
    Route::get('funds/{fund}/pdf', [FundController::class, 'exportPdf'])
        ->name('funds.pdf');
    Route::get('pdf-exports/{export}/status', [FundController::class, 'exportStatus'])
        ->name('funds.pdf.status');
    Route::get('pdf-exports/{export}/download', [FundController::class, 'downloadPdf'])
        ->name('funds.pdf.download');

    // AJAX endpoints for inline editing
    Route::patch('funds/{fund}/update-data', [FundController::class, 'updateData'])
        ->name('funds.update-data');
    Route::patch('funds/{fund}/update-holding', [FundController::class, 'updateHolding'])
        ->name('funds.update-holding');

    // Excel import
    Route::post('funds/{fund}/import', [FundController::class, 'import'])
        ->name('funds.import');
    Route::post('funds/{fund}/import-data/{month}', [FundController::class, 'importMonth'])
        ->where('month', '\d{4}-\d{2}')
        ->name('funds.import-month');

    // Revision management
    Route::get('funds/{fund}/revisions', [FundController::class, 'revisions'])
        ->name('funds.revisions');
    Route::get('funds/{fund}/revisions/{revision}', [FundController::class, 'showRevision'])
        ->name('funds.revisions.show');
    Route::post('funds/{fund}/revisions/{revision}/restore', [FundController::class, 'restoreRevision'])
        ->name('funds.revisions.restore');

    // Account management — admins only. See docs/users.md.
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/disable', [UserController::class, 'disable'])->name('users.disable');
        Route::post('users/{user}/enable', [UserController::class, 'enable'])->name('users.enable');
    });
});

require __DIR__.'/auth.php';
