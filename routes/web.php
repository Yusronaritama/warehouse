<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarehouseController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [WarehouseController::class, 'index'])->name('dashboard');
    Route::get('/logs', [WarehouseController::class, 'logs'])->name('logs.index');
    Route::post('/warehouse/items', [WarehouseController::class, 'storeItem'])->name('warehouse.items.store');
    Route::patch('/warehouse/items/{item}/location', [WarehouseController::class, 'updateLocation'])->name('warehouse.items.location');
    Route::post('/warehouse/items/{item}/mutate', [WarehouseController::class, 'mutateStock'])->name('warehouse.items.mutate');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
