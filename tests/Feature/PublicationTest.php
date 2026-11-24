<?php

namespace Tests\Feature;

use App\Events\PublicationCreee;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_create_publication(): void
    {
        $author = User::factory()->create(['role' => 'auteur']);
        $categorie = Category::factory()->create();

        $this->actingAs($author)
            ->post(route('contenus.store'), [
                'titre' => 'Publication de test valide',
                'contenu' => 'Contenu suffisamment long pour la validation.',
                'categorie_id' => $categorie->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('publications', [
            'titre' => 'Publication de test valide',
            'user_id' => $author->id,
        ]);
    }

    public function test_other_author_cannot_update_publication(): void
    {
        $author = User::factory()->create(['role' => 'auteur']);
        $other = User::factory()->create(['role' => 'auteur']);
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication protégée',
            'contenu' => 'Contenu appartenant à son auteur.',
            'categorie_id' => $categorie->id,
            'user_id' => $author->id,
        ]);

        $this->actingAs($other)
            ->put(route('contenus.update', $publication), [
                'titre' => 'Tentative interdite',
                'contenu' => 'Cette modification ne doit pas être enregistrée.',
                'categorie_id' => $categorie->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'titre' => 'Publication protégée',
        ]);
    }

    public function test_invalid_image_is_rejected(): void
    {
        $author = User::factory()->create(['role' => 'auteur']);
        $categorie = Category::factory()->create();

        $this->actingAs($author)
            ->from(route('contenus.create'))
            ->post(route('contenus.store'), [
                'titre' => 'Publication avec mauvais fichier',
                'contenu' => 'Contenu suffisamment long pour la validation.',
                'categorie_id' => $categorie->id,
                'image' => UploadedFile::fake()->create('document.txt', 100, 'text/plain'),
            ])
            ->assertRedirect(route('contenus.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('publications', [
            'titre' => 'Publication avec mauvais fichier',
        ]);
    }

    public function test_publication_created_event_logs_publication(): void
    {
        $author = User::factory()->create();
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Événement publication créée',
            'contenu' => 'Contenu suffisamment long pour cette publication.',
            'categorie_id' => $categorie->id,
            'user_id' => $author->id,
        ]);
        Log::spy();

        event(new PublicationCreee($publication));

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message, array $context) use ($publication): bool {
                return $message === 'Publication créée'
                    && $context['publication_id'] === $publication->id;
            })
            ->once();
    }
}
