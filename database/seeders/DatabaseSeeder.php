<?php

namespace Database\Seeders;

use App\Models\categorieProduit;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolesAndPermissionsSeeder::class);
        
        User::factory()->count(10)->create();
        $this->call(CategorieProduitSeeder::class);
        $this->call(ProduitSeeder::class);
        $this->call(CategorieRessourceSeeder::class);
        $this->call(RessourceSeeder::class);
        $this->call(ForumSeeder::class);
        $this->call(CommentaireSeeder::class);
        $this->call(EvenementSeeder::class);
        $this->call(ArticleSeeder::class);
    }
}
