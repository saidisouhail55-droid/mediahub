<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContenuController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriesController;

Route::get('/', [PageController::class, 'accueil'])->name('accueil');
Route::get('/a-propos', [PageController::class, 'aPropos'])->name('a-propos');

Route::resource('categories', CategoriesController::class)->parameters(['categories' => 'categorie']);
Route::resource('contenus', ContenuController::class)->parameters(['contenus' => 'publication']);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/tableau-de-bord', [AdminController::class, 'index'])->name('dashboard');
});
