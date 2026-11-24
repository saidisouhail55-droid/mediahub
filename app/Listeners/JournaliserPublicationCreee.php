<?php

namespace App\Listeners;

use App\Events\PublicationCreee;
use Illuminate\Support\Facades\Log;

class JournaliserPublicationCreee
{
    public function handle(PublicationCreee $event): void
    {
        Log::info('Publication créée', [
            'publication_id' => $event->publication->id,
            'titre' => $event->publication->titre,
            'user_id' => $event->publication->user_id,
        ]);
    }
}
