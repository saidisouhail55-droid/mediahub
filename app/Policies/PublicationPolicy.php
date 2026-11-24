<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    /**
     * Déterminer si l'utilisateur peut consulter la publication.
     */
    public function view(?User $user, Publication $publication): bool
    {
        return true;
    }

    /**
     * Déterminer si l'utilisateur peut modifier la publication :
     * Seul l'auteur propriétaire ou un administrateur peut modifier.
     */
    public function update(User $user, Publication $publication): bool
    {
        return $user->id === $publication->user_id || $user->role === 'admin';
    }

    /**
     * Déterminer si l'utilisateur peut supprimer la publication :
     * Seul l'auteur propriétaire ou un administrateur peut supprimer.
     */
    public function delete(User $user, Publication $publication): bool
    {
        return $user->id === $publication->user_id || $user->role === 'admin';
    }
}
