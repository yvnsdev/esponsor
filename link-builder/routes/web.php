<?php

use App\Http\Controllers\BuilderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return redirect()->route('builder.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/builder', [BuilderController::class, 'index'])->name('builder.index');
    Route::patch('/dashboard/builder/blocks', [BuilderController::class, 'updateBlocks'])->name('builder.update-blocks');
    Route::post('/dashboard/builder/publish', [BuilderController::class, 'publish'])->name('builder.publish');
    Route::post('/dashboard/builder/profile', [BuilderController::class, 'updateProfile'])->name('builder.update-profile');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['patch', 'post'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public pages
Route::get('/@{slug}', [PublicPageController::class, 'show'])->name('public.page');

require __DIR__.'/auth.php';
