<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategorieRequest;
use App\Http\Requests\UpdateCategorieRequest;
use App\Models\Categorie;
use Illuminate\Support\Facades\Gate;

class CategoriesController extends Controller
{
    /**
     * Afficher la liste des catégories avec pagination et nombre de publications associées.
     * Accès : Public.
     */
    public function index()
    {
        $categories = Categorie::withCount('publications')
            ->orderBy('nom')
            ->paginate(5);

        return view('categories.index', compact('categories'));
    }

    /**
     * Afficher le formulaire de création d'une nouvelle catégorie.
     * Autorisation : Gate 'gerer-categories' (Administrateur uniquement).
     */
    public function create()
    {
        Gate::authorize('gerer-categories');

        return view('categories.create');
    }

    /**
     * Enregistrer une nouvelle catégorie validée dans MySQL.
     * Autorisation : Gate 'gerer-categories' (Administrateur uniquement).
     */
    public function store(StoreCategorieRequest $request)
    {
        Gate::authorize('gerer-categories');

        Categorie::create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('succes', 'Catégorie créée avec succès.');
    }

    /**
     * Afficher les détails d'une catégorie et ses publications associées.
     * Accès : Public.
     */
    public function show(Categorie $categorie)
    {
        $categorie->load(['publications.auteur']);

        return view('categories.show', compact('categorie'));
    }

    /**
     * Afficher le formulaire de modification d'une catégorie existante.
     * Autorisation : Gate 'gerer-categories' (Administrateur uniquement).
     */
    public function edit(Categorie $categorie)
    {
        Gate::authorize('gerer-categories');

        return view('categories.edit', compact('categorie'));
    }

    /**
     * Mettre à jour une catégorie avec validation via UpdateCategorieRequest.
     * Autorisation : Gate 'gerer-categories' (Administrateur uniquement).
     */
    public function update(UpdateCategorieRequest $request, Categorie $categorie)
    {
        Gate::authorize('gerer-categories');

        $categorie->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('succes', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprimer une catégorie avec protection contre les contraintes d'intégrité référentielle.
     * Autorisation : Gate 'gerer-categories' (Administrateur uniquement).
     */
    public function destroy(Categorie $categorie)
    {
        Gate::authorize('gerer-categories');

        // Empêcher la suppression si des publications sont rattachées à la catégorie
        if ($categorie->publications()->count() > 0) {
            return redirect()
                ->route('categories.index')
                ->with('erreur', 'Impossible de supprimer cette catégorie car des publications y sont encore associées.');
        }

        $categorie->delete();

        return redirect()
            ->route('categories.index')
            ->with('succes', 'Catégorie supprimée avec succès.');
    }
}
