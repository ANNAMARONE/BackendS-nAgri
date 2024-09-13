<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorieRessourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
   
        DB::table('categorie_ressources')->insert([
            ['libelle' => 'Céréales', 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Légumes', 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Fruits', 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Produits laitiers', 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'Viandes', 'created_at' => now(), 'updated_at' => now()],
        ]);
    
    }
}
