<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Categorie;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
     *
     * Explication pédagogique — Semaine 9 :
     * 1. $request->validated() retourne uniquement les champs qui ont passé les règles.
     * 2. Pour un fichier, on appelle ->store('dossier', 'disque') qui :
     *    a) génère un nom de fichier unique (UUID),
     *    b) déplace le fichier temporaire de /tmp vers storage/app/public/dossier/,
     *    c) retourne le chemin relatif à enregistrer en base (ex: "publications/abc123.jpg").
     * 3. type_media est déterminé côté serveur via getMimeType(), jamais depuis le formulaire.
     * 4. user_id est toujours l'ID de l'utilisateur connecté, jamais fourni par le formulaire.
     *
     * Accès : Utilisateur connecté (auth).
     */
    public function store(StorePublicationRequest $request)
    {
        // On commence par les données texte validées (titre, contenu, categorie_id)
        $donnees = $request->safe()->only(['titre', 'contenu', 'categorie_id']);

        // Toujours l'ID de l'utilisateur authentifié — jamais modifiable par le formulaire
        $donnees['user_id'] = auth()->id();

        // === Traitement de l'image ===
        // Si un fichier image a été téléversé et validé :
        if ($request->hasFile('image')) {
            // store() : stocke dans storage/app/public/publications/
            // Retourne le chemin relatif : "publications/nom_unique.jpg"
            $donnees['image'] = $request->file('image')->store('publications', 'public');
            $this->genererMiniature($request->file('image'), $donnees['image']);
        }

        // === Traitement du fichier audio/vidéo ===
        if ($request->hasFile('fichier_media')) {
            $fichier = $request->file('fichier_media');

            // Stocker dans storage/app/public/medias/
            $donnees['fichier_media'] = $fichier->store('medias', 'public');

            // Déterminer le type MIME réel (côté serveur, non falsifiable)
            // getMimeType() lit les premiers octets du fichier pour identifier son type réel
            $mimeType = $fichier->getMimeType();
            $donnees['type_media'] = str_starts_with($mimeType, 'audio/') ? 'audio' : 'video';
        }

        // === Traitement du document privé ===
        // Stocké sur le disque 'local' (storage/app/private/) — non accessible depuis le web
        if ($request->hasFile('document_prive')) {
            $donnees['document_prive'] = $request->file('document_prive')->store('documents', 'local');
        }

        // === Enregistrement en base de données ===
        $publication = Publication::create($donnees);

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
     * Autorisation : Seul l'auteur propriétaire ou un admin peut modifier (PublicationPolicy).
     */
    public function edit(Publication $publication)
    {
        $this->authorize('update', $publication);

        $categories = Categorie::orderBy('nom')->get();

        return view('contenus.edit', compact('publication', 'categories'));
    }

    /**
     * Enregistrer les modifications validées avec UpdatePublicationRequest.
     *
     * Explication pédagogique — Remplacement sécurisé des fichiers :
     * Pour chaque fichier (image, fichier_media) :
     * 1. On vérifie l'autorisation (PublicationPolicy).
     * 2. On stocke le NOUVEAU fichier en premier (si téléversement).
     * 3. On mémorise l'ancien chemin AVANT de modifier le modèle.
     * 4. On met à jour le modèle en base.
     * 5. On supprime l'ancien fichier SEULEMENT si l'enregistrement réussit.
     * → Si l'étape 4 échoue, l'ancien fichier reste intact.
     *
     * Autorisation : Seul l'auteur propriétaire ou un admin peut modifier.
     */
    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        $this->authorize('update', $publication);

        $donnees = $request->safe()->only(['titre', 'contenu', 'categorie_id']);

        // === Remplacement de l'image ===
        $ancienneImage = null;
        if ($request->hasFile('image')) {
            $ancienneImage = $publication->image; // On sauvegarde l'ancien chemin
            $donnees['image'] = $request->file('image')->store('publications', 'public');
            $this->genererMiniature($request->file('image'), $donnees['image']);
        }

        // === Remplacement du fichier audio/vidéo ===
        $ancienFichierMedia = null;
        if ($request->hasFile('fichier_media')) {
            $ancienFichierMedia = $publication->fichier_media;
            $fichier = $request->file('fichier_media');
            $donnees['fichier_media'] = $fichier->store('medias', 'public');
            $mimeType = $fichier->getMimeType();
            $donnees['type_media'] = str_starts_with($mimeType, 'audio/') ? 'audio' : 'video';
        }

        // === Remplacement du document privé ===
        $ancienDocumentPrive = null;
        if ($request->hasFile('document_prive')) {
            $ancienDocumentPrive = $publication->document_prive;
            $donnees['document_prive'] = $request->file('document_prive')->store('documents', 'local');
        }

        // === Mise à jour en base de données ===
        $publication->update($donnees);

        // === Suppression des anciens fichiers (APRÈS succès de la mise à jour en base) ===
        if ($ancienneImage) {
            if (Storage::disk('public')->exists($ancienneImage)) {
                Storage::disk('public')->delete($ancienneImage);
            }
            $ancienneMiniature = Publication::cheminMiniature($ancienneImage);
            if ($ancienneMiniature && Storage::disk('public')->exists($ancienneMiniature)) {
                Storage::disk('public')->delete($ancienneMiniature);
            }
        }
        if ($ancienFichierMedia && Storage::disk('public')->exists($ancienFichierMedia)) {
            Storage::disk('public')->delete($ancienFichierMedia);
        }
        if ($ancienDocumentPrive && Storage::disk('local')->exists($ancienDocumentPrive)) {
            Storage::disk('local')->delete($ancienDocumentPrive);
        }

        return redirect()
            ->route('contenus.show', $publication)
            ->with('succes', 'Publication mise à jour avec succès.');
    }

    /**
     * Supprimer une publication (suppression logique avec SoftDeletes).
     *
     * Explication : SoftDeletes marque la publication comme supprimée (deleted_at)
     * sans retirer la ligne de MySQL. Les fichiers physiques sont supprimés
     * automatiquement par l'événement 'deleting' dans le modèle Publication.
     *
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

    /**
     * MINI-DÉFI SEMAINE 9 — Servir un document privé de façon sécurisée.
     *
     * Explication pédagogique :
     * Les fichiers sur le disque 'local' (storage/app/private/) ne sont JAMAIS
     * accessibles directement depuis le web (il n'y a pas de lien symbolique
     * pour ce dossier). Pour les servir, on doit les lire côté serveur et les
     * streamer dans la réponse HTTP après vérification des droits.
     *
     * Accès : Utilisateur connecté, propriétaire de la publication ou administrateur.
     */
    public function telechargerDocument(Publication $publication)
    {
        // Vérifier l'autorisation via PublicationPolicy (même règle que la modification)
        $this->authorize('update', $publication);

        // Vérifier qu'un document existe pour cette publication
        if (! $publication->document_prive) {
            abort(404, 'Aucun document disponible pour cette publication.');
        }

        // Vérifier que le fichier existe physiquement sur le disque
        if (! Storage::disk('local')->exists($publication->document_prive)) {
            abort(404, 'Le document demandé est introuvable sur le serveur.');
        }

        // Streamer le fichier dans la réponse (Content-Disposition: attachment)
        // Le chemin est lu depuis la base de données, jamais depuis une entrée utilisateur libre
        return Storage::disk('local')->download($publication->document_prive);
    }

    /**
     * Génère une miniature 400x300 (recadrage centré) avec l'extension GD de PHP.
     */
    private function genererMiniature(UploadedFile $fichier, string $cheminImage): void
    {
        if (! function_exists('imagecreatefromstring')) {
            return;
        }

        $contenu = @file_get_contents($fichier->getRealPath());
        if ($contenu === false) {
            return;
        }

        $source = @imagecreatefromstring($contenu);
        if ($source === false) {
            return;
        }

        $largeurSrc = imagesx($source);
        $hauteurSrc = imagesy($source);
        $largeurCible = 400;
        $hauteurCible = 300;

        $ratioSrc = $largeurSrc / max(1, $hauteurSrc);
        $ratioCible = $largeurCible / $hauteurCible;

        if ($ratioSrc > $ratioCible) {
            $hauteurCrop = $hauteurSrc;
            $largeurCrop = (int) round($hauteurSrc * $ratioCible);
            $x = (int) round(($largeurSrc - $largeurCrop) / 2);
            $y = 0;
        } else {
            $largeurCrop = $largeurSrc;
            $hauteurCrop = (int) round($largeurSrc / $ratioCible);
            $x = 0;
            $y = (int) round(($hauteurSrc - $hauteurCrop) / 2);
        }

        $miniature = imagecreatetruecolor($largeurCible, $hauteurCible);
        imagecopyresampled(
            $miniature,
            $source,
            0,
            0,
            $x,
            $y,
            $largeurCible,
            $hauteurCible,
            $largeurCrop,
            $hauteurCrop
        );

        ob_start();
        imagejpeg($miniature, null, 80);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($miniature);

        if ($jpeg !== false) {
            Storage::disk('public')->put(
                Publication::cheminMiniature($cheminImage),
                $jpeg
            );
        }
    }
}
