<?php

namespace Database\Factories;

use App\Models\Commentaire;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commentaire>
 */
class CommentaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contenu' => $this->faker->paragraph(),
            'publication_id' => Publication::factory(),
            'user_id' => User::factory(),
        ];
    }
}
