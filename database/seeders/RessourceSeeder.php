<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RessourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $ressources = [];

        for ($i = 1; $i <= 100; $i++) {
            $ressources[] = [
                'libelle' => 'Ressource ' . $i,
                'image' => 'image' . $i . '.jpg',
                'description' => 'Description de la ressource ' . $i,
                'piéce_join' => 'piece_join' . $i . '.pdf',
                'categorie_ressource_id' => rand(1, 5), // Assurez-vous que ces IDs de catégorie existent
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('ressources')->insert($ressources);
    
    }
}
