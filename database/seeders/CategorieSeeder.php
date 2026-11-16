<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::firstOrCreate(['slug' => 'video'], ['nom' => 'Vidéo']);
        Category::firstOrCreate(['slug' => 'audio'], ['nom' => 'Audio']);
        Category::firstOrCreate(['slug' => 'image'], ['nom' => 'Image']);
    }
}
