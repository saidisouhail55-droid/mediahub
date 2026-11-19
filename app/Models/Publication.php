<?php

namespace App\Models;

use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'publications';

    protected $fillable = ['titre', 'contenu', 'image', 'categorie_id', 'user_id'];

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
}
