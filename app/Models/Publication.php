<?php

namespace App\Models;

use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'publications';

    /**
     * Champs autorisés à l'assignation en masse (mass assignment).
     *
     * Semaine 9 : ajout de fichier_media, type_media et document_prive.
     * Note : user_id est toujours fixé côté serveur via auth()->id(),
     * jamais fourni directement par le formulaire.
     */
    protected $fillable = [
        'titre',
        'contenu',
        'image',
        'fichier_media',
        'type_media',
        'document_prive',
        'categorie_id',
        'user_id',
    ];

    // =====================================================================
    // RELATIONS ELOQUENT
    // =====================================================================

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function category(): BelongsTo
    {
        return $this->categorie();
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->auteur();
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class, 'publication_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'publication_tag');
    }

    // =====================================================================
    // ÉVÉNEMENTS DU MODÈLE — SUPPRESSION AUTOMATIQUE DES FICHIERS
    // =====================================================================

    /**
     * Semaine 9 — Mini-défi : suppression automatique des fichiers physiques
     * lorsqu'une publication est supprimée (SoftDeletes déclenche aussi cet événement).
     *
     * Explication pédagogique :
     * L'événement 'deleting' est intercepté avant que Laravel exécute le DELETE
     * en base de données. On supprime les fichiers en premier. Si la suppression
     * fichier échoue, le modèle n'est pas supprimé (la transaction est annulée).
     *
     * ATTENTION : Publication::where(...)->delete() (suppression de masse)
     * ne déclenche PAS les événements de modèle. Toujours utiliser
     * $publication->delete() sur une instance pour garantir ce comportement.
     */
    /**
     * Retourne le chemin relatif de la miniature associée à une image.
     */
    public static function cheminMiniature(?string $cheminImage): ?string
    {
        return $cheminImage ? 'publications/miniatures/' . basename($cheminImage) : null;
    }

    /**
     * Retourne l'URL publique de la miniature (ou de l'image originale en secours).
     */
    public function getUrlMiniatureAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        $miniature = self::cheminMiniature($this->image);
        if ($miniature && Storage::disk('public')->exists($miniature)) {
            return Storage::disk('public')->url($miniature);
        }

        return Storage::disk('public')->url($this->image);
    }

    protected static function booted(): void
    {
        static::deleting(function (Publication $publication) {
            // Supprimer l'image du disque public et sa miniature si elles existent
            if ($publication->image) {
                if (Storage::disk('public')->exists($publication->image)) {
                    Storage::disk('public')->delete($publication->image);
                }
                $miniature = self::cheminMiniature($publication->image);
                if ($miniature && Storage::disk('public')->exists($miniature)) {
                    Storage::disk('public')->delete($miniature);
                }
            }

            // Supprimer le fichier audio/vidéo du disque public si il existe
            if ($publication->fichier_media && Storage::disk('public')->exists($publication->fichier_media)) {
                Storage::disk('public')->delete($publication->fichier_media);
            }

            // Supprimer le document privé du disque local si il existe
            if ($publication->document_prive && Storage::disk('local')->exists($publication->document_prive)) {
                Storage::disk('local')->delete($publication->document_prive);
            }
        });
    }
}
