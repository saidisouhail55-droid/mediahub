<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContenuController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\AuthController;

// Pages publiques institutionnelles
Route::get('/', [PageController::class, 'accueil'])->name('accueil');
Route::get('/a-propos', [PageController::class, 'aPropos'])->name('a-propos');

// Routes d'authentification (réservées aux invités / non connectés)
Route::middleware('guest')->group(function () {
    Route::get('/inscription', [AuthController::class, 'create'])->name('inscription');
    Route::post('/inscription', [AuthController::class, 'store']);
    Route::get('/connexion', [AuthController::class, 'creerConnexion'])->name('connexion');
    Route::get('/login', [AuthController::class, 'creerConnexion'])->name('login'); // Alias pour le middleware auth de Laravel
    Route::post('/connexion', [AuthController::class, 'connecter']);
});

// Déconnexion (réservée aux utilisateurs connectés)
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'deconnecter'])->name('deconnexion');
});

// Catégories : consultation publique, actions de gestion protégées par Gate dans le contrôleur
Route::resource('categories', CategoriesController::class)->parameters(['categories' => 'categorie']);

// Publications (Contenus) :
// 1. Écriture protégée par le middleware 'auth' (création, modification, suppression)
// Déclaré avant 'show' afin que /contenus/create ne soit pas capturé par le paramètre dynamique {publication}
Route::middleware('auth')->group(function () {
    Route::resource('contenus', ContenuController::class)
        ->except(['index', 'show'])
        ->parameters(['contenus' => 'publication']);
});

// 2. Consultation publique (liste et détail)
Route::resource('contenus', ContenuController::class)
    ->only(['index', 'show'])
    ->parameters(['contenus' => 'publication']);

// Espace d'administration
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/tableau-de-bord', [AdminController::class, 'index'])->name('dashboard');
});
