<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PublicationController extends Controller
{
    public function index()
    {
        $publications = Publication::with(['categorie', 'auteur'])
            ->latest()
            ->when(request()->filled('categorie_id'), function ($query): void {
                $query->where('categorie_id', request()->integer('categorie_id'));
            })
            ->paginate(10);

        return PublicationResource::collection($publications);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $donnees = $request->safe()->only(['titre', 'contenu', 'categorie_id']);
        $donnees['user_id'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $donnees['image'] = $request->file('image')->store('publications', 'public');
        }

        if ($request->hasFile('fichier_media')) {
            $fichier = $request->file('fichier_media');
            $donnees['fichier_media'] = $fichier->store('medias', 'public');
            $mimeType = $fichier->getMimeType();
            $donnees['type_media'] = str_starts_with($mimeType, 'audio/') ? 'audio' : 'video';
        }

        if ($request->hasFile('document_prive')) {
            $donnees['document_prive'] = $request->file('document_prive')->store('documents', 'local');
        }

        $publication = Publication::create($donnees);

        return (new PublicationResource($publication->load(['categorie', 'auteur'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Publication $publication): PublicationResource
    {
        return new PublicationResource(
            $publication->load(['categorie', 'auteur'])
        );
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): PublicationResource
    {
        $this->authorize('update', $publication);

        $donnees = $request->safe()->only(['titre', 'contenu', 'categorie_id']);

        $ancienneImage = null;
        if ($request->hasFile('image')) {
            $ancienneImage = $publication->image;
            $donnees['image'] = $request->file('image')->store('publications', 'public');
        }

        $ancienFichierMedia = null;
        if ($request->hasFile('fichier_media')) {
            $ancienFichierMedia = $publication->fichier_media;
            $fichier = $request->file('fichier_media');
            $donnees['fichier_media'] = $fichier->store('medias', 'public');
            $mimeType = $fichier->getMimeType();
            $donnees['type_media'] = str_starts_with($mimeType, 'audio/') ? 'audio' : 'video';
        }

        $ancienDocumentPrive = null;
        if ($request->hasFile('document_prive')) {
            $ancienDocumentPrive = $publication->document_prive;
            $donnees['document_prive'] = $request->file('document_prive')->store('documents', 'local');
        }

        $publication->update($donnees);

        if ($ancienneImage) {
            Storage::disk('public')->delete($ancienneImage);
        }
        if ($ancienFichierMedia) {
            Storage::disk('public')->delete($ancienFichierMedia);
        }
        if ($ancienDocumentPrive) {
            Storage::disk('local')->delete($ancienDocumentPrive);
        }

        return new PublicationResource(
            $publication->load(['categorie', 'auteur'])
        );
    }

    public function destroy(Publication $publication): Response
    {
        $this->authorize('delete', $publication);

        $publication->delete();

        return response()->noContent();
    }
}
