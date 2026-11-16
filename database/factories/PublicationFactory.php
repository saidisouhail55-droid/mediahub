<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => $this->faker->unique()->sentence(6),
            'contenu' => $this->faker->paragraphs(3, true),
            'image' => null,
            'categorie_id' => Category::factory(),
            'user_id' => User::factory(),
        ];
    }
}
