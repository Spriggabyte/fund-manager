<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Special route for internal PDF generation (bypasses auth)
Route::get('/internal/funds/{fund}/pdf-view', [\App\Http\Controllers\FundController::class, 'internalPdfView'])
    ->name('funds.internal.pdf-view');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('funds', \App\Http\Controllers\FundController::class);

    // Fact sheet view
    Route::get('funds/{fund}/fact-sheet', [\App\Http\Controllers\FundController::class, 'factSheet'])
        ->name('funds.fact-sheet');

    // PDF export
    Route::get('funds/{fund}/pdf', [\App\Http\Controllers\FundController::class, 'exportPdf'])
        ->name('funds.pdf');

    // AJAX endpoints for inline editing
    Route::patch('funds/{fund}/update-data', [\App\Http\Controllers\FundController::class, 'updateData'])
        ->name('funds.update-data');
    Route::patch('funds/{fund}/update-holding', [\App\Http\Controllers\FundController::class, 'updateHolding'])
        ->name('funds.update-holding');

    // Revision management
    Route::get('funds/{fund}/revisions', [\App\Http\Controllers\FundController::class, 'revisions'])
        ->name('funds.revisions');
    Route::get('funds/{fund}/revisions/{revision}', [\App\Http\Controllers\FundController::class, 'showRevision'])
        ->name('funds.revisions.show');
    Route::post('funds/{fund}/revisions/{revision}/restore', [\App\Http\Controllers\FundController::class, 'restoreRevision'])
        ->name('funds.revisions.restore');
});

require __DIR__.'/auth.php';
