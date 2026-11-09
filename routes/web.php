<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ImageController::class, 'index'])->name('images.index');
Route::post('/images', [ImageController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('images.store');
Route::delete('/images', [ImageController::class, 'destroyAll'])
    ->name('images.destroy-all');
Route::delete('/images/{filename}', [ImageController::class, 'destroy'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('images.destroy');
