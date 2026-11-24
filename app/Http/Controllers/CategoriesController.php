<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    /**
     * Afficher la liste des catégories avec pagination et nombre de publications associées.
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
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Enregistrer une nouvelle catégorie dans la base de données MySQL.
     */
    public function store(Request $request)
    {
        $nom = $request->input('nom');
        $slug = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($nom);

        Categorie::create([
            'nom' => $nom,
            'slug' => $slug,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('succes', 'Catégorie créée avec succès.');
    }

    /**
     * Afficher les détails d'une catégorie et ses publications associées.
     */
    public function show(Categorie $categorie)
    {
        $categorie->load(['publications.auteur']);

        return view('categories.show', compact('categorie'));
    }

    /**
     * Afficher le formulaire de modification d'une catégorie existante.
     */
    public function edit(Categorie $categorie)
    {
        return view('categories.edit', compact('categorie'));
    }

    /**
     * Mettre à jour une catégorie dans MySQL.
     */
    public function update(Request $request, Categorie $categorie)
    {
        $nom = $request->input('nom');
        $slug = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($nom);

        $categorie->update([
            'nom' => $nom,
            'slug' => $slug,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('succes', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprimer une catégorie avec protection contre les contraintes d'intégrité référentielle.
     */
    public function destroy(Categorie $categorie)
    {
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
