<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('articles')->insert([
                'libelle' => 'Article ' . $i,
                'image' => 'image' . $i . '.jpg',
                'description' => 'Ceci est la description de l\'article ' . $i . '.',
                'date' => now(),
                'lien' => 'https://example.com/article' . $i,
                'statut' => $i % 2 == 0 ? 'publié' : 'brouillon',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
    
    }
}
