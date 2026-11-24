<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $this->post(route('inscription'), [
            'name' => 'Amel',
            'email' => 'amel@exemple.com',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ])
            ->assertRedirect(route('accueil'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'amel@exemple.com',
            'role' => 'auteur',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'amel@exemple.com',
            'password' => Hash::make('motdepasse'),
        ]);

        $this->post(route('connexion'), [
            'email' => 'amel@exemple.com',
            'password' => 'motdepasse',
        ])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'amel@exemple.com',
            'password' => Hash::make('motdepasse'),
        ]);

        $this->from(route('connexion'))
            ->post(route('connexion'), [
                'email' => 'amel@exemple.com',
                'password' => 'incorrect',
            ])
            ->assertRedirect(route('connexion'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
