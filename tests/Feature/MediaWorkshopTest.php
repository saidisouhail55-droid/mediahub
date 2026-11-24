<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Categorie;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests de la Semaine 9 — Gestion des médias dans MediaHub
 *
 * Chaque test utilise Storage::fake('public') et Storage::fake('local') pour
 * simuler le disque de stockage sans écrire de vrais fichiers sur le disque.
 * UploadedFile::fake() génère des fichiers de test sans données réelles.
 *
 * RefreshDatabase réinitialise la base de données de test avant chaque test.
 */
class MediaWorkshopTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // TEST 1 : Création sans média — fonctionne comme avant
    // =====================================================================

    public function test_creation_publication_sans_media(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('contenus.store'), [
            'titre'        => 'Publication Sans Aucun Média',
            'contenu'      => 'Contenu textuel sans fichier joint, suffisamment long.',
            'categorie_id' => $categorie->id,
        ]);

        $publication = Publication::where('titre', 'Publication Sans Aucun Média')->first();
        $this->assertNotNull($publication);
        $response->assertRedirect(route('contenus.show', $publication));

        // Aucun fichier ne doit être enregistré
        $this->assertNull($publication->image);
        $this->assertNull($publication->fichier_media);
        $this->assertNull($publication->type_media);
    }

    // =====================================================================
    // TEST 2 : Création avec une image valide
    // =====================================================================

    public function test_creation_publication_avec_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // UploadedFile::fake()->image() crée un vrai fichier image PNG en mémoire
        $imageFake = UploadedFile::fake()->image('couverture.jpg', 800, 600);

        $response = $this->actingAs($user)->post(route('contenus.store'), [
            'titre'        => 'Publication Avec Une Belle Image',
            'contenu'      => 'Découvrez cette magnifique publication illustrée.',
            'categorie_id' => $categorie->id,
            'image'        => $imageFake,
        ]);

        $publication = Publication::where('titre', 'Publication Avec Une Belle Image')->first();
        $this->assertNotNull($publication);
        $this->assertNotNull($publication->image);

        // Vérifier que le chemin commence par 'publications/'
        $this->assertStringStartsWith('publications/', $publication->image);

        // Vérifier que le fichier existe réellement sur le disque simulé
        Storage::disk('public')->assertExists($publication->image);
        Storage::disk('public')->assertExists(Publication::cheminMiniature($publication->image));

        $response->assertRedirect(route('contenus.show', $publication));
    }

    // =====================================================================
    // TEST 3 : Création avec un fichier audio
    // =====================================================================

    public function test_creation_publication_avec_audio(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // Créer un faux fichier MP3 (10 Ko)
        $audioFake = UploadedFile::fake()->create('podcast.mp3', 10, 'audio/mpeg');

        $response = $this->actingAs($user)->post(route('contenus.store'), [
            'titre'          => 'Publication Avec Podcast Audio',
            'contenu'        => 'Écoutez ce podcast de qualité sur les nouvelles technologies.',
            'categorie_id'   => $categorie->id,
            'fichier_media'  => $audioFake,
        ]);

        $publication = Publication::where('titre', 'Publication Avec Podcast Audio')->first();
        $this->assertNotNull($publication);
        $this->assertNotNull($publication->fichier_media);
        $this->assertEquals('audio', $publication->type_media);
        $this->assertStringStartsWith('medias/', $publication->fichier_media);
        Storage::disk('public')->assertExists($publication->fichier_media);
    }

    // =====================================================================
    // TEST 4 : Création avec un fichier vidéo
    // =====================================================================

    public function test_creation_publication_avec_video(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $videoFake = UploadedFile::fake()->create('video.mp4', 500, 'video/mp4');

        $response = $this->actingAs($user)->post(route('contenus.store'), [
            'titre'          => 'Publication Avec Vidéo Documentaire',
            'contenu'        => 'Visionnez ce documentaire exclusif sur la vie marine.',
            'categorie_id'   => $categorie->id,
            'fichier_media'  => $videoFake,
        ]);

        $publication = Publication::where('titre', 'Publication Avec Vidéo Documentaire')->first();
        $this->assertNotNull($publication);
        $this->assertNotNull($publication->fichier_media);
        $this->assertEquals('video', $publication->type_media);
        Storage::disk('public')->assertExists($publication->fichier_media);
    }

    // =====================================================================
    // TEST 5 : Téléversement d'un fichier invalide (texte déguisé en image)
    // =====================================================================

    public function test_telechargement_fichier_invalide_rejete(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // Crée un fichier texte avec l'extension .jpg — doit être rejeté
        $fichierInvalide = UploadedFile::fake()->create('script.php', 100, 'text/plain');

        $response = $this->actingAs($user)
            ->from(route('contenus.create'))
            ->post(route('contenus.store'), [
                'titre'        => 'Publication Avec Fichier Invalide',
                'contenu'      => 'Tentative de téléversement de fichier malveillant.',
                'categorie_id' => $categorie->id,
                'image'        => $fichierInvalide,
            ]);

        // Le fichier invalide doit déclencher une erreur de validation
        $response->assertSessionHasErrors('image');

        // Aucune publication ne doit avoir été créée
        $this->assertDatabaseMissing('publications', [
            'titre' => 'Publication Avec Fichier Invalide',
        ]);
    }

    // =====================================================================
    // TEST 6 : Fichier trop volumineux rejeté
    // =====================================================================

    public function test_image_trop_volumineuse_rejetee(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // 3000 Ko = 3 Mo — dépasse la limite de 2048 Ko (2 Mo)
        $imageTropGrande = UploadedFile::fake()->image('trop-grande.jpg')->size(3000);

        $response = $this->actingAs($user)
            ->from(route('contenus.create'))
            ->post(route('contenus.store'), [
                'titre'        => 'Publication Image Trop Grande Test',
                'contenu'      => 'Contenu pour tester le rejet des images trop volumineuses.',
                'categorie_id' => $categorie->id,
                'image'        => $imageTropGrande,
            ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('publications', [
            'titre' => 'Publication Image Trop Grande Test',
        ]);
    }

    // =====================================================================
    // TEST 7 : Modification sans remplacer les médias (fichiers conservés)
    // =====================================================================

    public function test_modification_sans_remplacer_media_conserve_les_fichiers(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // Simuler une image existante sur le disque
        Storage::disk('public')->put('publications/image-existante.jpg', 'fake-image-content');

        $publication = Publication::create([
            'titre'        => 'Publication Avec Image Originale Existante',
            'contenu'      => 'Contenu de la publication originale avant modification.',
            'categorie_id' => $categorie->id,
            'user_id'      => $user->id,
            'image'        => 'publications/image-existante.jpg',
        ]);

        // Modifier uniquement le texte, sans nouveau fichier image
        $response = $this->actingAs($user)->put(route('contenus.update', $publication), [
            'titre'        => 'Publication Avec Image Originale Existante',
            'contenu'      => 'Contenu mis à jour sans toucher à l\'image. Plus de vingt caractères.',
            'categorie_id' => $categorie->id,
        ]);

        $response->assertRedirect(route('contenus.show', $publication));

        $publication->refresh();
        // L'image doit être conservée (non supprimée, non modifiée)
        $this->assertEquals('publications/image-existante.jpg', $publication->image);
        Storage::disk('public')->assertExists('publications/image-existante.jpg');
    }

    // =====================================================================
    // TEST 8 : Remplacement d'une image — l'ancienne est supprimée
    // =====================================================================

    public function test_remplacement_image_supprime_ancienne(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // Créer l'ancienne image simulée et sa miniature sur le disque
        Storage::disk('public')->put('publications/ancienne-image.jpg', 'old-content');
        Storage::disk('public')->put('publications/miniatures/ancienne-image.jpg', 'old-thumb');

        $publication = Publication::create([
            'titre'        => 'Publication Dont L\'Image Va Être Remplacée',
            'contenu'      => 'Contenu original avec ancienne image à remplacer.',
            'categorie_id' => $categorie->id,
            'user_id'      => $user->id,
            'image'        => 'publications/ancienne-image.jpg',
        ]);

        $nouvelleImage = UploadedFile::fake()->image('nouvelle-couverture.png', 400, 300);

        $response = $this->actingAs($user)->put(route('contenus.update', $publication), [
            'titre'        => 'Publication Dont L\'Image Va Être Remplacée',
            'contenu'      => 'Contenu mis à jour avec la nouvelle image.',
            'categorie_id' => $categorie->id,
            'image'        => $nouvelleImage,
        ]);

        $response->assertRedirect(route('contenus.show', $publication));

        $publication->refresh();

        // La nouvelle image et sa miniature doivent être enregistrées
        $this->assertNotNull($publication->image);
        $this->assertNotEquals('publications/ancienne-image.jpg', $publication->image);
        Storage::disk('public')->assertExists($publication->image);
        Storage::disk('public')->assertExists(Publication::cheminMiniature($publication->image));

        // L'ancienne image et son ancienne miniature doivent avoir été supprimées
        Storage::disk('public')->assertMissing('publications/ancienne-image.jpg');
        Storage::disk('public')->assertMissing('publications/miniatures/ancienne-image.jpg');
    }

    // =====================================================================
    // TEST 9 : Suppression d'une publication — les fichiers sont supprimés
    // =====================================================================

    public function test_suppression_publication_supprime_ses_fichiers(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        // Créer les fichiers fictifs
        Storage::disk('public')->put('publications/image-a-supprimer.jpg', 'img');
        Storage::disk('public')->put('publications/miniatures/image-a-supprimer.jpg', 'thumb');
        Storage::disk('public')->put('medias/audio-a-supprimer.mp3', 'audio');
        Storage::disk('local')->put('documents/doc-a-supprimer.pdf', 'pdf');

        $publication = Publication::create([
            'titre'          => 'Publication À Supprimer Avec Ses Fichiers',
            'contenu'        => 'Cette publication et ses médias vont disparaître.',
            'categorie_id'   => $categorie->id,
            'user_id'        => $user->id,
            'image'          => 'publications/image-a-supprimer.jpg',
            'fichier_media'  => 'medias/audio-a-supprimer.mp3',
            'type_media'     => 'audio',
            'document_prive' => 'documents/doc-a-supprimer.pdf',
        ]);

        $response = $this->actingAs($user)->delete(route('contenus.destroy', $publication));

        $response->assertRedirect(route('contenus.index'));

        // La publication doit être soft-deleted (non visible par défaut)
        $this->assertNull(Publication::find($publication->id));

        // Les fichiers physiques (dont la miniature) doivent avoir été supprimés
        Storage::disk('public')->assertMissing('publications/image-a-supprimer.jpg');
        Storage::disk('public')->assertMissing('publications/miniatures/image-a-supprimer.jpg');
        Storage::disk('public')->assertMissing('medias/audio-a-supprimer.mp3');
        Storage::disk('local')->assertMissing('documents/doc-a-supprimer.pdf');
    }

    // =====================================================================
    // TEST 10 : Visiteur refusé pour le document privé
    // =====================================================================

    public function test_visiteur_ne_peut_pas_telecharger_document_prive(): void
    {
        $user = User::factory()->create();
        $categorie = Category::factory()->create();

        $publication = Publication::create([
            'titre'          => 'Publication Avec Document Privé Protégé',
            'contenu'        => 'Cette publication contient un document privé.',
            'categorie_id'   => $categorie->id,
            'user_id'        => $user->id,
            'document_prive' => 'documents/secret.pdf',
        ]);

        // Un visiteur non connecté doit être redirigé vers la connexion
        $response = $this->get(route('contenus.document', $publication));
        $response->assertRedirect(route('login'));
    }

    // =====================================================================
    // TEST 11 : Accès refusé à un autre auteur pour le document privé
    // =====================================================================

    public function test_autre_auteur_ne_peut_pas_telecharger_document_prive(): void
    {
        Storage::fake('local');

        $auteurProprio = User::factory()->create();
        $autreAuteur   = User::factory()->create();
        $categorie     = Category::factory()->create();

        Storage::disk('local')->put('documents/secret.pdf', 'PDF content');

        $publication = Publication::create([
            'titre'          => 'Publication Document Accès Interdit Autre Auteur',
            'contenu'        => 'Cette publication contient un document protégé.',
            'categorie_id'   => $categorie->id,
            'user_id'        => $auteurProprio->id,
            'document_prive' => 'documents/secret.pdf',
        ]);

        // L'autre auteur tente d'accéder au document → 403
        $response = $this->actingAs($autreAuteur)->get(route('contenus.document', $publication));
        $response->assertStatus(403);
    }

    // =====================================================================
    // TEST 12 : L'auteur propriétaire peut télécharger son document
    // =====================================================================

    public function test_auteur_proprietaire_peut_telecharger_son_document(): void
    {
        Storage::fake('local');

        $auteur    = User::factory()->create();
        $categorie = Category::factory()->create();

        Storage::disk('local')->put('documents/mon-doc.pdf', 'Contenu PDF');

        $publication = Publication::create([
            'titre'          => 'Publication Document Auteur Accès Autorisé',
            'contenu'        => 'Cette publication appartient à l\'auteur.',
            'categorie_id'   => $categorie->id,
            'user_id'        => $auteur->id,
            'document_prive' => 'documents/mon-doc.pdf',
        ]);

        // L'auteur propriétaire peut télécharger son document → 200
        $response = $this->actingAs($auteur)->get(route('contenus.document', $publication));
        $response->assertStatus(200);
    }

    // =====================================================================
    // TEST 13 : L'administrateur peut télécharger n'importe quel document
    // =====================================================================

    public function test_admin_peut_telecharger_tout_document(): void
    {
        Storage::fake('local');

        $auteur    = User::factory()->create();
        $admin     = User::factory()->create(['role' => 'admin']);
        $categorie = Category::factory()->create();

        Storage::disk('local')->put('documents/doc-admin-test.pdf', 'Contenu PDF Admin');

        $publication = Publication::create([
            'titre'          => 'Publication Document Accès Admin Global',
            'contenu'        => 'L\'administrateur peut accéder à tous les documents.',
            'categorie_id'   => $categorie->id,
            'user_id'        => $auteur->id,
            'document_prive' => 'documents/doc-admin-test.pdf',
        ]);

        // L'administrateur peut accéder au document d'un autre auteur → 200
        $response = $this->actingAs($admin)->get(route('contenus.document', $publication));
        $response->assertStatus(200);
    }

    // =====================================================================
    // TEST 14 : Affichage correct des médias dans la page de détail
    // =====================================================================

    public function test_page_detail_affiche_les_medias(): void
    {
        Storage::fake('public');

        $user      = User::factory()->create();
        $categorie = Category::factory()->create();

        Storage::disk('public')->put('publications/detail-img.jpg', 'img');
        Storage::disk('public')->put('medias/detail-audio.mp3', 'audio');

        $publication = Publication::create([
            'titre'         => 'Publication Affichage Médias Détail',
            'contenu'       => 'Contenu de la publication avec image et audio à afficher.',
            'categorie_id'  => $categorie->id,
            'user_id'       => $user->id,
            'image'         => 'publications/detail-img.jpg',
            'fichier_media' => 'medias/detail-audio.mp3',
            'type_media'    => 'audio',
        ]);

        $response = $this->get(route('contenus.show', $publication));

        $response->assertStatus(200);
        // La balise audio doit être présente dans la page
        $response->assertSee('<audio', false);
        // L'image doit être dans le code HTML
        $response->assertSee('detail-img.jpg', false);
    }

    // =====================================================================
    // TEST 15 : Un visiteur non connecté peut consulter sans accéder aux fichiers privés
    // =====================================================================

    public function test_visiteur_peut_consulter_publications_sans_acceder_aux_prives(): void
    {
        Storage::fake('public');

        $user      = User::factory()->create();
        $categorie = Category::factory()->create();

        Storage::disk('public')->put('publications/image-publique.jpg', 'img');

        $publication = Publication::create([
            'titre'         => 'Publication Consultation Publique Autorisée',
            'contenu'       => 'Cette publication est visible par tous les visiteurs.',
            'categorie_id'  => $categorie->id,
            'user_id'       => $user->id,
            'image'         => 'publications/image-publique.jpg',
        ]);

        // Un visiteur peut voir la liste
        $this->get(route('contenus.index'))->assertStatus(200)->assertSee('Publication Consultation Publique Autorisée');

        // Un visiteur peut voir le détail
        $this->get(route('contenus.show', $publication))->assertStatus(200);

        // Un visiteur est redirigé s'il tente de créer
        $this->get(route('contenus.create'))->assertRedirect(route('login'));
    }
}

