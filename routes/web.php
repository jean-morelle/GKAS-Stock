<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MyExitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\StockExitController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    // Commun : le contenu dépend du rôle.
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Sorties : le technicien enregistre ce qu'il prend, le responsable peut aussi le faire.
    Route::resource('stock/exits', StockExitController::class)
        ->only(['create', 'store'])
        ->names('stock.exits');

    // Espace technicien.
    Route::middleware('role:technicien')->group(function () {
        Route::get('/mes-sorties', MyExitController::class)->name('my-exits.index');
    });

    // Espace responsable.
    Route::middleware('role:responsable')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class)->except('index');
        Route::resource('technicians', TechnicianController::class);

        Route::prefix('stock')->name('stock.')->group(function () {
            Route::resource('entries', StockEntryController::class)->only(['index', 'create', 'store']);
            Route::get('exits', [StockExitController::class, 'index'])->name('exits.index');
            Route::get('movements', StockMovementController::class)->name('movements.index');
            Route::get('alerts', StockAlertController::class)->name('alerts.index');
        });
    });
});

require __DIR__.'/auth.php';
