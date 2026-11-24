<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Categorie;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndPolicyWorkshopTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================
     * TEST 1 : VISITEUR (NON CONNECTÉ)
     * ========================================================= */

    public function test_visiteur_peut_consulter_les_publications_mais_pas_creer_ni_acceder_aux_actions_protegees(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Publication Publique Découverte',
            'contenu' => 'Contenu accessible à tous les visiteurs anonymes.',
            'categorie_id' => $categorie->id,
            'user_id' => $user->id,
        ]);

        // 1. Consultation publique liste et détail autorisée (200 OK)
        $this->get(route('contenus.index'))->assertStatus(200)->assertSee('Publication Publique Découverte');
        $this->get(route('contenus.show', $publication))->assertStatus(200)->assertSee('Publication Publique Découverte');

        // 2. Tentative d'accès à la création : redirection vers la connexion
        $this->get(route('contenus.create'))->assertRedirect(route('login'));

        // 3. Tentative de soumission d'une publication : redirection
        $this->post(route('contenus.store'), [
            'titre' => 'Titre Pirate Anonyme',
            'contenu' => 'Contenu qui ne doit pas être enregistré par un anonyme.',
            'categorie_id' => $categorie->id,
        ])->assertRedirect(route('login'));

        // 4. Tentative de création de catégorie sans droits : refusé (403 Forbidden)
        $this->get(route('categories.create'))->assertStatus(403);
    }

    /* =========================================================
     * TEST 2 : AUTEUR A
     * ========================================================= */

    public function test_auteur_a_peut_gerer_sa_publication_mais_pas_celle_de_b(): void
    {
        $auteurA = User::factory()->create(['name' => 'Auteur A', 'role' => 'auteur']);
        $auteurB = User::factory()->create(['name' => 'Auteur B', 'role' => 'auteur']);
        $categorie = Category::factory()->create();

        // Création par l'auteur A
        $response = $this->actingAs($auteurA)->post(route('contenus.store'), [
            'titre' => 'Publication de Auteur A',
            'contenu' => 'Contenu rédigé par Auteur A avec plus de 20 caractères.',
            'categorie_id' => $categorie->id,
        ]);

        $pubA = Publication::where('titre', 'Publication de Auteur A')->first();
        $this->assertNotNull($pubA);
        $this->assertEquals($auteurA->id, $pubA->user_id);
        $response->assertRedirect(route('contenus.show', $pubA));

        // Auteur A peut modifier sa propre publication
        $this->actingAs($auteurA)->get(route('contenus.edit', $pubA))->assertStatus(200);

        $this->actingAs($auteurA)->put(route('contenus.update', $pubA), [
            'titre' => 'Titre Modifié par Auteur A',
            'contenu' => 'Contenu mis à jour par l\'auteur légitime.',
            'categorie_id' => $categorie->id,
        ])->assertRedirect(route('contenus.show', $pubA));

        // Publication créée par Auteur B
        $pubB = Publication::create([
            'titre' => 'Publication Privée de B',
            'contenu' => 'Ce contenu appartient strictement à Auteur B.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteurB->id,
        ]);

        // Auteur A ne peut PAS modifier la publication de B (403 Forbidden via Policy)
        $this->actingAs($auteurA)->get(route('contenus.edit', $pubB))->assertStatus(403);

        $this->actingAs($auteurA)->put(route('contenus.update', $pubB), [
            'titre' => 'Piratage par Auteur A',
            'contenu' => 'Tentative de modification non autorisée.',
            'categorie_id' => $categorie->id,
        ])->assertStatus(403);

        // Auteur A ne peut PAS supprimer la publication de B (403 Forbidden via Policy)
        $this->actingAs($auteurA)->delete(route('contenus.destroy', $pubB))->assertStatus(403);

        // Auteur A peut supprimer sa propre publication
        $this->actingAs($auteurA)->delete(route('contenus.destroy', $pubA))->assertRedirect(route('contenus.index'));
        $this->assertNull(Publication::find($pubA->id));
        $this->assertNotNull(Publication::withTrashed()->find($pubA->id));
    }

    /* =========================================================
     * TEST 3 : AUTEUR B
     * ========================================================= */

    public function test_auteur_b_ne_peut_pas_modifier_ni_supprimer_les_publications_de_a(): void
    {
        $auteurA = User::factory()->create(['name' => 'Auteur A', 'role' => 'auteur']);
        $auteurB = User::factory()->create(['name' => 'Auteur B', 'role' => 'auteur']);
        $categorie = Category::factory()->create();

        $pubA = Publication::create([
            'titre' => 'Contenu Authentique de A',
            'contenu' => 'Description appartenant exclusivement à Auteur A.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteurA->id,
        ]);

        // Auteur B tente d'éditer la publication de A
        $this->actingAs($auteurB)->get(route('contenus.edit', $pubA))->assertStatus(403);

        // Auteur B tente de mettre à jour la publication de A
        $this->actingAs($auteurB)->put(route('contenus.update', $pubA), [
            'titre' => 'Altération Malveillante',
            'contenu' => 'Texte modifié indûment par Auteur B.',
            'categorie_id' => $categorie->id,
        ])->assertStatus(403);

        // Auteur B tente de supprimer la publication de A
        $this->actingAs($auteurB)->delete(route('contenus.destroy', $pubA))->assertStatus(403);
    }

    /* =========================================================
     * TEST 4 : ADMINISTRATEUR
     * ========================================================= */

    public function test_administrateur_peut_modifier_supprimer_toutes_les_publications_et_gerer_categories(): void
    {
        $admin = User::factory()->create(['name' => 'Admin MediaHub', 'role' => 'admin']);
        $auteur = User::factory()->create(['name' => 'Auteur Normal', 'role' => 'auteur']);
        $categorie = Category::factory()->create(['nom' => 'Catégorie Test', 'slug' => 'categorie-test']);

        $pub = Publication::create([
            'titre' => 'Publication Auteur Simple',
            'contenu' => 'Contenu rédigé par un auteur normal.',
            'categorie_id' => $categorie->id,
            'user_id' => $auteur->id,
        ]);

        // 1. L'admin peut éditer la publication de n'importe quel auteur
        $this->actingAs($admin)->get(route('contenus.edit', $pub))->assertStatus(200);

        // 2. L'admin peut mettre à jour la publication
        $this->actingAs($admin)->put(route('contenus.update', $pub), [
            'titre' => 'Titre Modifié par Admin',
            'contenu' => 'Modéré et corrigé par l\'administrateur.',
            'categorie_id' => $categorie->id,
        ])->assertRedirect(route('contenus.show', $pub));

        // 3. L'admin peut créer une catégorie (Gate 'gerer-categories')
        $this->actingAs($admin)->get(route('categories.create'))->assertStatus(200);

        $this->actingAs($admin)->post(route('categories.store'), [
            'nom' => 'Nouvelle Catégorie Admin',
            'slug' => 'nouvelle-categorie-admin',
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'nouvelle-categorie-admin']);

        // 4. L'admin peut supprimer la publication
        $this->actingAs($admin)->delete(route('contenus.destroy', $pub))->assertRedirect(route('contenus.index'));
        $this->assertNull(Publication::find($pub->id));
    }

    /* =========================================================
     * TEST 5 : DÉCONNEXION ET CYCLE AUTH
     * ========================================================= */

    public function test_inscription_connexion_et_deconnexion(): void
    {
        // 1. Inscription d'un nouvel utilisateur
        $responseInscription = $this->post(route('inscription'), [
            'name' => 'Nouveau Membre',
            'email' => 'nouveau@mediahub.test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $responseInscription->assertRedirect(route('accueil'));
        $this->assertAuthenticated();

        $user = User::where('email', 'nouveau@mediahub.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('auteur', $user->role);
        $this->assertTrue(Hash::check('secret1234', $user->password));

        // 2. Déconnexion
        $responseDeconnexion = $this->post(route('deconnexion'));
        $responseDeconnexion->assertRedirect(route('accueil'));
        $this->assertGuest();

        // Après déconnexion, l'accès à une page protégée redirige vers la connexion
        $this->get(route('contenus.create'))->assertRedirect(route('login'));

        // 3. Reconnexion avec les identifiants créés
        $responseConnexion = $this->post(route('connexion'), [
            'email' => 'nouveau@mediahub.test',
            'password' => 'secret1234',
        ]);

        // Redirection vers l'URL initialement souhaitée (intended URL : contenus.create)
        $responseConnexion->assertRedirect(route('contenus.create'));
        $this->assertAuthenticatedAs($user);
    }
}
