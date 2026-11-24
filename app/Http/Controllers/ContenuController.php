<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Categorie;
use App\Models\Publication;
use App\Models\User;

class ContenuController extends Controller
{
    /**
     * Afficher la liste paginée des publications avec chargement anticipé (eager loading).
     */
    public function index()
    {
        $publications = Publication::with(['categorie', 'auteur'])
            ->withCount('commentaires')
            ->latest()
            ->paginate(9);

        return view('contenus.index', compact('publications'));
    }

    /**
     * Afficher le formulaire de création d'une nouvelle publication.
     */
    public function create()
    {
        $categories = Categorie::orderBy('nom')->get();

        return view('contenus.create', compact('categories'));
    }

    /**
     * Enregistrer une nouvelle publication validée dans MySQL.
     * Protection contre le Mass Assignment : seules les données validées sont utilisées,
     * et user_id est contrôlé strictement côté serveur.
     */
    public function store(StorePublicationRequest $request)
    {
        // Récupérer un utilisateur existant dans la base MySQL (démonstration)
        $user = User::first();
        $userId = $user ? $user->id : 1;

        $publication = Publication::create([
            ...$request->validated(),
            'user_id' => $userId,
        ]);

        return redirect()
            ->route('contenus.show', $publication)
            ->with('succes', 'Publication créée avec succès.');
    }

    /**
     * Afficher le détail d'une publication, ses relations et ses commentaires.
     */
    public function show(Publication $publication)
    {
        $publication->load(['categorie', 'auteur', 'commentaires.auteur', 'tags']);

        return view('contenus.show', compact('publication'));
    }

    /**
     * Afficher le formulaire de modification d'une publication.
     */
    public function edit(Publication $publication)
    {
        $categories = Categorie::orderBy('nom')->get();

        return view('contenus.edit', compact('publication', 'categories'));
    }

    /**
     * Enregistrer les modifications validées avec UpdatePublicationRequest.
     */
    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        $publication->update($request->validated());

        return redirect()
            ->route('contenus.show', $publication)
            ->with('succes', 'Publication mise à jour avec succès.');
    }

    /**
     * Supprimer une publication (suppression logique avec SoftDeletes).
     */
    public function destroy(Publication $publication)
    {
        $publication->delete();

        return redirect()
            ->route('contenus.index')
            ->with('succes', 'Publication supprimée avec succès.');
    }
}
