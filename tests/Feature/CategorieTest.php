<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategorieTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_visible(): void
    {
        Category::factory()->create(['nom' => 'Podcast']);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Podcast');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('categories.store'), [
                'nom' => 'Podcast',
                'slug' => 'podcast',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'nom' => 'Podcast',
            'slug' => 'podcast',
        ]);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create(['role' => 'auteur']);

        $this->actingAs($user)
            ->post(route('categories.store'), [
                'nom' => 'Interdit',
                'slug' => 'interdit',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['slug' => 'interdit']);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $categorie = Category::factory()->create([
            'nom' => 'Ancienne',
            'slug' => 'ancienne',
        ]);

        $this->actingAs($admin)
            ->put(route('categories.update', $categorie), [
                'nom' => 'Nouvelle',
                'slug' => 'nouvelle',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $categorie->id,
            'nom' => 'Nouvelle',
            'slug' => 'nouvelle',
        ]);
    }

    public function test_category_validation_rejects_missing_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'nom' => '',
                'slug' => 'sans-nom',
            ])
            ->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors('nom');

        $this->assertDatabaseMissing('categories', ['slug' => 'sans-nom']);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $categorie = Category::factory()->create();

        $this->actingAs($admin)
            ->delete(route('categories.destroy', $categorie))
            ->assertRedirect(route('categories.index'));

        $this->assertModelMissing($categorie);
    }

    public function test_category_cache_is_created_and_invalidated_after_creation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('categories.index'))->assertOk();
        $this->assertTrue(Cache::has('categories.liste'));

        $this->actingAs($admin)
            ->post(route('categories.store'), [
                'nom' => 'Catégorie mise en cache',
                'slug' => 'categorie-mise-en-cache',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertFalse(Cache::has('categories.liste'));
    }
}
