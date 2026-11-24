<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkshopTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_list_and_detail_are_public(): void
    {
        $auteur = User::factory()->create();
        $categorie = Category::factory()->create(['nom' => 'Musique']);
        $publication = Publication::create([
            'titre' => 'Publication publique MediaHub',
            'contenu' => 'Contenu suffisamment détaillé pour MediaHub.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteur->id,
        ]);

        $this->getJson('/api/publications')
            ->assertOk()
            ->assertJsonPath('data.0.id', $publication->id)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);

        $this->getJson("/api/publications/{$publication->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $publication->id)
            ->assertJsonPath('data.categorie.nom', 'Musique');
    }

    public function test_category_list_includes_publication_count_and_category_filter_works(): void
    {
        $auteur = User::factory()->create();
        $categorie = Category::factory()->create(['nom' => 'Cinéma']);
        $autreCategorie = Category::factory()->create(['nom' => 'Audio']);

        Publication::create([
            'titre' => 'Film documentaire MediaHub',
            'contenu' => 'Description détaillée du documentaire.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteur->id,
        ]);
        Publication::create([
            'titre' => 'Podcast musical MediaHub',
            'contenu' => 'Description détaillée du podcast musical.',
            'categorie_id' => $autreCategorie->id,
            'user_id' => $auteur->id,
        ]);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonFragment([
                'nom' => 'Cinéma',
                'nombre_publications' => 1,
            ]);

        $this->getJson('/api/publications?categorie_id='.$categorie->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.categorie.id', $categorie->id);
    }

    public function test_write_routes_require_a_sanctum_token(): void
    {
        $categorie = Category::factory()->create();

        $this->postJson('/api/publications', [
            'titre' => 'Titre protégé de API',
            'contenu' => 'Contenu protégé suffisamment long.',
            'categorie_id' => $categorie->id,
        ])->assertUnauthorized();

        $auteur = User::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication à supprimer',
            'contenu' => 'Contenu à supprimer sans jeton.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteur->id,
        ]);

        $this->deleteJson("/api/publications/{$publication->id}")
            ->assertUnauthorized();
    }

    public function test_login_returns_bearer_token_and_authenticated_user_can_create(): void
    {
        $user = User::factory()->create([
            'email' => 'api@mediahub.test',
            'password' => 'password',
        ]);
        $categorie = Category::factory()->create();

        $login = $this->postJson('/api/connexion', [
            'email' => 'api@mediahub.test',
            'password' => 'password',
        ]);

        $login->assertOk()
            ->assertJsonStructure(['token', 'token_type'])
            ->assertJsonPath('token_type', 'Bearer');

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/publications', [
                'titre' => 'Publication créée par API',
                'contenu' => 'Contenu créé via le point d’accès REST.',
                'categorie_id' => $categorie->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.auteur.id', $user->id);

        $this->assertDatabaseHas('publications', [
            'titre' => 'Publication créée par API',
            'user_id' => $user->id,
        ]);
    }

    public function test_invalid_credentials_return_unauthorized(): void
    {
        $this->postJson('/api/connexion', [
            'email' => 'inconnu@mediahub.test',
            'password' => 'incorrect',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'Identifiants incorrects.']);
    }

    public function test_validation_error_returns_422_and_resource_is_not_created(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/publications', [
            'titre' => 'court',
            'contenu' => 'court',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titre', 'contenu', 'categorie_id']);

        $this->assertDatabaseMissing('publications', ['titre' => 'court']);
    }

    public function test_user_cannot_update_or_delete_another_users_publication(): void
    {
        $auteur = User::factory()->create();
        $autreUtilisateur = User::factory()->create();
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication appartenant à auteur',
            'contenu' => 'Contenu appartenant à un autre utilisateur.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteur->id,
        ]);

        Sanctum::actingAs($autreUtilisateur);

        $this->putJson("/api/publications/{$publication->id}", [
            'titre' => 'Tentative de modification interdite',
            'contenu' => 'Ce contenu ne doit pas être enregistré.',
            'categorie_id' => $categorie->id,
        ])->assertForbidden();

        $this->deleteJson("/api/publications/{$publication->id}")
            ->assertForbidden();

        $this->assertModelExists($publication);
    }

    public function test_owner_can_update_and_delete_their_publication(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication originale API',
            'contenu' => 'Contenu original suffisamment long.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/publications/{$publication->id}", [
            'titre' => 'Publication modifiée API',
            'contenu' => 'Contenu modifié suffisamment long.',
            'categorie_id' => $categorie->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.titre', 'Publication modifiée API');

        $this->deleteJson("/api/publications/{$publication->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('publications', ['id' => $publication->id]);
    }
}
