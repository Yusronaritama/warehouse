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
    Route::put('/warehouse/items/{item}', [WarehouseController::class, 'updateItem'])->name('warehouse.items.update');
    Route::delete('/warehouse/items/{item}', [WarehouseController::class, 'destroyItem'])->name('warehouse.items.destroy');
    Route::patch('/warehouse/items/{item}/location', [WarehouseController::class, 'updateLocation'])->name('warehouse.items.location');
    Route::post('/warehouse/items/{item}/mutate', [WarehouseController::class, 'mutateStock'])->name('warehouse.items.mutate');

    // Rack Dedicated CRUD & Management
    Route::get('/racks/{code}', [\App\Http\Controllers\RackController::class, 'show'])->name('racks.show');
    Route::put('/racks/{code}', [\App\Http\Controllers\RackController::class, 'update'])->name('racks.update');

    // Barcode Management & Rack Generator
    Route::get('/barcodes', [\App\Http\Controllers\BarcodeController::class, 'index'])->name('barcodes.index');
    Route::get('/barcodes/racks-list', [\App\Http\Controllers\BarcodeController::class, 'racksList'])->name('barcodes.racksList');
    Route::post('/barcodes', [\App\Http\Controllers\BarcodeController::class, 'store'])->name('barcodes.store');
    Route::patch('/barcodes/{barcode}/toggle', [\App\Http\Controllers\BarcodeController::class, 'toggleStatus'])->name('barcodes.toggle');
    Route::delete('/barcodes/{barcode}', [\App\Http\Controllers\BarcodeController::class, 'destroy'])->name('barcodes.destroy');
    Route::post('/barcodes/scan', [\App\Http\Controllers\BarcodeController::class, 'scan'])->name('barcodes.scan');
    Route::get('/barcodes/scan', [\App\Http\Controllers\BarcodeController::class, 'handleScanGet']);
    Route::get('/scan/{code?}', [\App\Http\Controllers\BarcodeController::class, 'handleScanGet'])->name('scan.get');
    Route::post('/barcodes/{barcode}/print-log', [\App\Http\Controllers\BarcodeController::class, 'logPrint'])->name('barcodes.printLog');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
