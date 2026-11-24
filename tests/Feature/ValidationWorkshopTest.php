<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Categorie;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationWorkshopTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================
     * TESTS DE VALIDATION DES CATÉGORIES
     * ========================================================= */

    public function test_categorie_creation_echoue_si_nom_manquant(): void
    {
        $response = $this->post(route('categories.store'), [
            'nom' => '',
            'slug' => 'test-slug',
        ]);

        $response->assertSessionHasErrors('nom');
        $this->assertDatabaseMissing('categories', ['slug' => 'test-slug']);
    }

    public function test_categorie_creation_echoue_si_slug_deja_existant(): void
    {
        Category::factory()->create(['slug' => 'podcast']);

        $response = $this->from(route('categories.create'))->post(route('categories.store'), [
            'nom' => 'Nouveau Podcast',
            'slug' => 'podcast',
        ]);

        $response->assertRedirect(route('categories.create'));
        $response->assertSessionHasErrors('slug');
    }

    public function test_categorie_modification_ignore_propre_slug(): void
    {
        $categorie = Category::factory()->create(['nom' => 'Musique', 'slug' => 'musique']);

        $response = $this->put(route('categories.update', $categorie), [
            'nom' => 'Musique & Audio',
            'slug' => 'musique', // Même slug : doit être accepté grâce à Rule::unique()->ignore()
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('succes');
        $this->assertDatabaseHas('categories', [
            'id' => $categorie->id,
            'nom' => 'Musique & Audio',
            'slug' => 'musique',
        ]);
    }

    /* =========================================================
     * TESTS DE VALIDATION DES PUBLICATIONS
     * ========================================================= */

    public function test_publication_creation_echoue_si_titre_trop_court(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $response = $this->from(route('contenus.create'))->post(route('contenus.store'), [
            'titre' => 'Court', // < 10 caractères
            'contenu' => 'Voici un contenu suffisamment long pour passer la validation de 20 caractères.',
            'categorie_id' => $categorie->id,
        ]);

        $response->assertRedirect(route('contenus.create'));
        $response->assertSessionHasErrors('titre');
    }

    public function test_publication_creation_echoue_si_titre_deja_utilise(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        Publication::create([
            'titre' => 'Titre Déjà Existant Unique',
            'contenu' => 'Un premier contenu descriptif.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->from(route('contenus.create'))->post(route('contenus.store'), [
            'titre' => 'Titre Déjà Existant Unique', // Titre dupliqué
            'contenu' => 'Un second contenu descriptif bien rédigé.',
            'categorie_id' => $categorie->id,
        ]);

        $response->assertRedirect(route('contenus.create'));
        $response->assertSessionHasErrors('titre');
    }

    public function test_publication_creation_echoue_si_contenu_trop_court(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $response = $this->from(route('contenus.create'))->post(route('contenus.store'), [
            'titre' => 'Titre Valide De Plus De 10 Caractères',
            'contenu' => 'Trop court', // < 20 caractères
            'categorie_id' => $categorie->id,
        ]);

        $response->assertRedirect(route('contenus.create'));
        $response->assertSessionHasErrors('contenu');
    }

    public function test_publication_creation_echoue_si_categorie_inexistante(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('contenus.create'))->post(route('contenus.store'), [
            'titre' => 'Titre Valide De Plus De 10 Caractères',
            'contenu' => 'Voici un contenu suffisamment long pour passer la validation de 20 caractères.',
            'categorie_id' => 99999, // Catégorie inexistante
        ]);

        $response->assertRedirect(route('contenus.create'));
        $response->assertSessionHasErrors('categorie_id');
    }

    public function test_publication_creation_reussie_avec_donnees_valides(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $response = $this->post(route('contenus.store'), [
            'titre' => 'Superbe Publication Valide',
            'contenu' => 'Voici un contenu descriptif complet avec largement plus de vingt caractères.',
            'categorie_id' => $categorie->id,
        ]);

        $pub = Publication::where('titre', 'Superbe Publication Valide')->first();
        $this->assertNotNull($pub);
        $response->assertRedirect(route('contenus.show', $pub));
        $response->assertSessionHas('succes');
    }

    public function test_publication_modification_ignore_propre_titre(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();
        $pub = Publication::create([
            'titre' => 'Titre Intact Lors Modif',
            'contenu' => 'Texte de départ avec au moins 20 caractères.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        $response = $this->put(route('contenus.update', $pub), [
            'titre' => 'Titre Intact Lors Modif', // Même titre
            'contenu' => 'Nouveau texte mis à jour avec plus de vingt caractères.',
            'categorie_id' => $categorie->id,
        ]);

        $response->assertRedirect(route('contenus.show', $pub));
        $response->assertSessionHas('succes');
    }

    public function test_mass_assignment_empeche_injection_arbitraire_de_user_id(): void
    {
        $userVrai = User::factory()->create();
        $userPirate = User::factory()->create();
        $categorie = Category::factory()->create();

        // Envoi d'une tentative d'usurpation avec user_id = $userPirate->id dans la requête
        $response = $this->post(route('contenus.store'), [
            'titre' => 'Publication Anti Mass Assignment',
            'contenu' => 'Contenu rédigé avec rigueur et sécurité pour valider la protection.',
            'categorie_id' => $categorie->id,
            'user_id' => $userPirate->id, // Doit être ignoré par le contrôleur et FormRequest
        ]);

        $pub = Publication::where('titre', 'Publication Anti Mass Assignment')->first();
        $this->assertNotNull($pub);
        // L'auteur ne doit PAS être l'utilisateur pirate injecté arbitrairement
        $this->assertEquals($userVrai->id, $pub->user_id);
    }
}
