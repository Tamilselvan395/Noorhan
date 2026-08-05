<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::view('/settings/profile', 'settings.profile')->name('settings.profile');
    Route::view('/settings/security', 'settings.security')->name('settings.security');

    Route::get('/leads', [App\Http\Controllers\LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [App\Http\Controllers\LeadController::class, 'show'])->name('leads.show');

    
    
});

// Public lead capture
Route::middleware('guest')->group(function () {
    Route::get('/capture/lead', [App\Http\Controllers\Capture\WebFormController::class, 'show'])->name('capture.web');
    Route::post('/capture/lead', [App\Http\Controllers\Capture\WebFormController::class, 'store'])->name('capture.web.store')->middleware('throttle:10,1');
    Route::get('/capture/success', [App\Http\Controllers\Capture\WebFormController::class, 'success'])->name('capture.web.success');
});