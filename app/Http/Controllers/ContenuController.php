<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Categorie;
use App\Models\Publication;
use Illuminate\Http\Request;

class ContenuController extends Controller
{
    /**
     * Afficher la liste paginée des publications avec chargement anticipé (eager loading).
     * Accès : Public.
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
     * Accès : Utilisateur connecté (auth).
     */
    public function create()
    {
        $categories = Categorie::orderBy('nom')->get();

        return view('contenus.create', compact('categories'));
    }

    /**
     * Enregistrer une nouvelle publication validée dans MySQL.
     * Accès : Utilisateur connecté (auth).
     * Sécurité : user_id est systématiquement lié à l'utilisateur connecté via auth()->id().
     */
    public function store(StorePublicationRequest $request)
    {
        $publication = Publication::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('contenus.show', $publication)
            ->with('succes', 'Publication créée avec succès.');
    }

    /**
     * Afficher le détail d'une publication, ses relations et ses commentaires.
     * Accès : Public.
     */
    public function show(Publication $publication)
    {
        $publication->load(['categorie', 'auteur', 'commentaires.auteur', 'tags']);

        return view('contenus.show', compact('publication'));
    }

    /**
     * Afficher le formulaire de modification d'une publication.
     * Autorisation : Seul l'auteur propriétaire ou un admin peut modifier.
     */
    public function edit(Publication $publication)
    {
        $this->authorize('update', $publication);

        $categories = Categorie::orderBy('nom')->get();

        return view('contenus.edit', compact('publication', 'categories'));
    }

    /**
     * Enregistrer les modifications validées avec UpdatePublicationRequest.
     * Autorisation : Seul l'auteur propriétaire ou un admin peut modifier.
     */
    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        $this->authorize('update', $publication);

        $publication->update($request->validated());

        return redirect()
            ->route('contenus.show', $publication)
            ->with('succes', 'Publication mise à jour avec succès.');
    }

    /**
     * Supprimer une publication (suppression logique avec SoftDeletes).
     * Autorisation : Seul l'auteur propriétaire ou un admin peut supprimer.
     */
    public function destroy(Publication $publication)
    {
        $this->authorize('delete', $publication);

        $publication->delete();

        return redirect()
            ->route('contenus.index')
            ->with('succes', 'Publication supprimée avec succès.');
    }
}
