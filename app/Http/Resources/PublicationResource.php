<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'contenu' => $this->contenu,
            'categorie' => [
                'id' => $this->categorie?->id,
                'nom' => $this->categorie?->nom,
            ],
            'auteur' => [
                'id' => $this->auteur?->id,
                'name' => $this->auteur?->name,
            ],
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'fichier_media' => $this->fichier_media ? Storage::disk('public')->url($this->fichier_media) : null,
            'type_media' => $this->type_media,
            'publie_le' => $this->created_at?->toDateString(),
        ];
    }
}
