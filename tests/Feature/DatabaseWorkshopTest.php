<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DatabaseWorkshopTest extends TestCase
{
    use RefreshDatabase;

    public function test_publications_can_have_a_null_image(): void
    {
        $publication = Publication::factory()->create(['image' => null]);

        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'image' => null,
        ]);
    }

    public function test_publications_reject_duplicate_titles(): void
    {
        Publication::factory()->create(['titre' => 'Titre unique']);

        $this->expectException(QueryException::class);

        Publication::factory()->create(['titre' => 'Titre unique']);
    }

    public function test_database_seeder_registers_idempotent_categories_and_admin(): void
    {
        Config::set('services.admin', [
            'name' => 'Administrateur MediaHub',
            'email' => 'admin@mediahub.test',
            'password' => 'test-password',
        ]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('categories', 3);
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check(
            'test-password',
            User::query()->where('email', 'admin@mediahub.test')->firstOrFail()->password,
        ));
    }

    public function test_admin_seeder_requires_configured_credentials(): void
    {
        Config::set('services.admin', [
            'name' => '',
            'email' => '',
            'password' => '',
        ]);

        $this->expectException(RuntimeException::class);

        $this->app->make(AdminSeeder::class)->run();
    }
}
