<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Categorie;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudWorkshopTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================
     * TESTS DU CRUD DES CATÉGORIES
     * ========================================================= */

    public function test_categories_index_displays_categories_list(): void
    {
        Category::factory()->create(['nom' => 'Musique', 'slug' => 'musique']);

        $response = $this->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Musique');
        $response->assertSee('+ Nouvelle catégorie');
    }

    public function test_categories_create_form_is_accessible(): void
    {
        $response = $this->get(route('categories.create'));

        $response->assertStatus(200);
        $response->assertSee('Créer une catégorie');
        $response->assertSee('Nom de la catégorie');
    }

    public function test_categories_store_creates_new_category_and_redirects(): void
    {
        $response = $this->post(route('categories.store'), [
            'nom' => 'Documentaires',
            'slug' => 'documentaires',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('succes', 'Catégorie créée avec succès.');

        $this->assertDatabaseHas('categories', [
            'nom' => 'Documentaires',
            'slug' => 'documentaires',
        ]);
    }

    public function test_categories_show_displays_category_and_its_publications(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create(['nom' => 'Cinéma', 'slug' => 'cinema']);
        Publication::create([
            'titre' => 'Film classique',
            'contenu' => 'Description détaillée du film classique.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('categories.show', $categorie));

        $response->assertStatus(200);
        $response->assertSee('Cinéma');
        $response->assertSee('Film classique');
    }

    public function test_categories_edit_displays_form_with_current_data(): void
    {
        $categorie = Category::factory()->create(['nom' => 'Série', 'slug' => 'serie']);

        $response = $this->get(route('categories.edit', $categorie));

        $response->assertStatus(200);
        $response->assertSee('Modifier la catégorie : Série');
        $response->assertSee('serie');
    }

    public function test_categories_update_modifies_data_and_redirects(): void
    {
        $categorie = Category::factory()->create(['nom' => 'Ancien Nom', 'slug' => 'ancien-nom']);

        $response = $this->put(route('categories.update', $categorie), [
            'nom' => 'Nouveau Nom',
            'slug' => 'nouveau-nom',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('succes', 'Catégorie mise à jour avec succès.');

        $this->assertDatabaseHas('categories', [
            'id' => $categorie->id,
            'nom' => 'Nouveau Nom',
            'slug' => 'nouveau-nom',
        ]);
    }

    public function test_categories_destroy_deletes_empty_category(): void
    {
        $categorie = Category::factory()->create(['nom' => 'À Supprimer', 'slug' => 'a-supprimer']);

        $response = $this->delete(route('categories.destroy', $categorie));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('succes', 'Catégorie supprimée avec succès.');

        $this->assertDatabaseMissing('categories', [
            'id' => $categorie->id,
        ]);
    }

    public function test_categories_destroy_prevents_deletion_if_publications_exist(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create(['nom' => 'Non Supprimable', 'slug' => 'non-supprimable']);
        Publication::create([
            'titre' => 'Publication rattachée',
            'contenu' => 'Contenu rattaché à la catégorie.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->delete(route('categories.destroy', $categorie));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('erreur');

        // La catégorie ne doit pas être supprimée
        $this->assertDatabaseHas('categories', [
            'id' => $categorie->id,
        ]);
    }

    /* =========================================================
     * TESTS DU CRUD DES PUBLICATIONS
     * ========================================================= */

    public function test_contenus_index_displays_publications_list(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();
        Publication::create([
            'titre' => 'Vidéo Découverte',
            'contenu' => 'Contenu passionnant sur les nouvelles technologies.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('contenus.index'));

        $response->assertStatus(200);
        $response->assertSee('Vidéo Découverte');
        $response->assertSee('+ Nouveau contenu');
    }

    public function test_contenus_create_displays_form_with_categories(): void
    {
        Category::factory()->create(['nom' => 'Photographie']);

        $response = $this->get(route('contenus.create'));

        $response->assertStatus(200);
        $response->assertSee('Créer une nouvelle publication');
        $response->assertSee('Photographie');
    }

    public function test_contenus_store_creates_publication_and_redirects(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $response = $this->post(route('contenus.store'), [
            'titre' => 'Titre de ma nouvelle publication',
            'contenu' => 'Un texte descriptif pour cette publication multimédia.',
            'categorie_id' => $categorie->id,
        ]);

        $publication = Publication::where('titre', 'Titre de ma nouvelle publication')->first();
        $this->assertNotNull($publication);

        $response->assertRedirect(route('contenus.show', $publication));
        $response->assertSessionHas('succes', 'Publication créée avec succès.');

        $this->assertDatabaseHas('publications', [
            'titre' => 'Titre de ma nouvelle publication',
            'categorie_id' => $categorie->id,
        ]);
    }

    public function test_contenus_edit_displays_form_with_publication_data(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication Avant Modif',
            'contenu' => 'Description initiale.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('contenus.edit', $publication));

        $response->assertStatus(200);
        $response->assertSee('Modifier la publication');
        $response->assertSee('Publication Avant Modif');
    }

    public function test_contenus_update_modifies_publication_and_redirects(): void
    {
        $user = User::factory()->create();
        $categorie1 = Category::factory()->create(['nom' => 'Cat 1']);
        $categorie2 = Category::factory()->create(['nom' => 'Cat 2']);
        $publication = Publication::create([
            'titre' => 'Ancien Titre Pub',
            'contenu' => 'Ancien contenu.',
            'categorie_id' => $categorie1->id,
            'user_id' => $user->id,
        ]);

        $response = $this->put(route('contenus.update', $publication), [
            'titre' => 'Nouveau Titre Pub',
            'contenu' => 'Nouveau contenu mis à jour.',
            'categorie_id' => $categorie2->id,
        ]);

        $response->assertRedirect(route('contenus.show', $publication));
        $response->assertSessionHas('succes', 'Publication mise à jour avec succès.');

        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'titre' => 'Nouveau Titre Pub',
            'categorie_id' => $categorie2->id,
        ]);
    }

    public function test_contenus_destroy_deletes_publication_and_redirects(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication à supprimer',
            'contenu' => 'Contenu qui va disparaître.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->delete(route('contenus.destroy', $publication));

        $response->assertRedirect(route('contenus.index'));
        $response->assertSessionHas('succes', 'Publication supprimée avec succès.');

        // Vérifier que la suppression logique (SoftDeletes) fonctionne
        $this->assertNull(Publication::find($publication->id));
        $this->assertNotNull(Publication::withTrashed()->find($publication->id));
    }
}
