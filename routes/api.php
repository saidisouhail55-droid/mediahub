<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicationController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::post('/connexion', [AuthController::class, 'connecter'])->name('api.connexion');

Route::get('/categories', function () {
    return response()->json(
        Category::query()
            ->withCount('publications')
            ->orderBy('nom')
            ->get(['id', 'nom', 'slug'])
            ->map(fn (Category $categorie): array => [
                'id' => $categorie->id,
                'nom' => $categorie->nom,
                'slug' => $categorie->slug,
                'nombre_publications' => $categorie->publications_count,
            ])
            ->values()
    );
})->name('api.categories.index');

Route::apiResource('publications', PublicationController::class)
    ->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('publications', PublicationController::class)
        ->except(['index', 'show']);
});
