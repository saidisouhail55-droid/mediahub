<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Categorie;
use App\Models\Commentaire;
use App\Models\Publication;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentWorkshopTest extends TestCase
{
    use RefreshDatabase;

    public function test_publications_relations_work_correctly(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Titre de test Eloquent',
            'contenu' => 'Contenu détaillé pour tester les relations Eloquent.',
            'categorie_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $comment = Commentaire::create([
            'contenu' => 'Mon premier commentaire',
            'publication_id' => $publication->id,
            'user_id' => $user->id,
        ]);

        $tag = Tag::create(['nom' => 'Laravel', 'slug' => 'laravel']);
        $publication->tags()->attach($tag->id);

        $this->assertEquals($category->id, $publication->categorie->id);
        $this->assertEquals($user->id, $publication->auteur->id);
        $this->assertCount(1, $publication->commentaires);
        $this->assertEquals($user->id, $comment->auteur->id);
        $this->assertCount(1, $publication->tags);
        $this->assertEquals('Laravel', $publication->tags->first()->nom);
    }

    public function test_publications_index_renders_successfully(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Publication::create([
            'titre' => 'Article Public 1',
            'contenu' => 'Contenu de l\'article public 1.',
            'categorie_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('contenus.index'));

        $response->assertStatus(200);
        $response->assertSee('Contenus multimédias');
        $response->assertSee('Article Public 1');
    }

    public function test_publication_show_renders_successfully(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Article Détaillé',
            'contenu' => 'Texte complet de l\'article détaillé.',
            'categorie_id' => $category->id,
            'user_id' => $user->id,
        ]);

        Commentaire::create([
            'contenu' => 'Commentaire sur cet article',
            'publication_id' => $publication->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('contenus.show', $publication));

        $response->assertStatus(200);
        $response->assertSee('Article Détaillé');
        $response->assertSee('Commentaire sur cet article');
    }

    public function test_soft_deletes_on_publication(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $publication = Publication::create([
            'titre' => 'Article à supprimer',
            'contenu' => 'Contenu de test.',
            'categorie_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $id = $publication->id;
        $publication->delete();

        $this->assertNull(Publication::find($id));
        $this->assertNotNull(Publication::withTrashed()->find($id));

        $publication->restore();
        $this->assertNotNull(Publication::find($id));
    }
}
